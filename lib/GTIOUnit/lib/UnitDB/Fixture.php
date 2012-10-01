<?

namespace GTIOUnit\UnitDB;

use Spyc,
    Gotron\Config,
    __;

class Fixture extends UnitDB {

    public function __construct($fixture_path = null) {
        if(is_null($fixture_path)) {
            if(defined('FIXTURE_PATH')) {
                $fixture_path = FIXTURE_PATH;
            }
            else {
                $fixture_path = __DIR__ . "/../../../fixtures/";
            }
        }
        if(is_dir($fixture_path)) {
            $this->fixture_path = $fixture_path;
        }
        else{
            throw new \Exception( "Fixture Path does not exist: " . $fixture_path );
        }
        parent::__construct();
    }

    public function create($name, $added_attributes = array()) {
        $fixture_file = $this->fixture_path . "$name.yaml";
        $fixture_file_json = $this->fixture_path . "$name.json";
        if(file_exists($fixture_file)) {
            $fixture = Spyc::YAMLLoad($fixture_file);
        }
        else if(file_exists($fixture_file_json)) {
            $fixture = json_decode(file_get_contents($fixture_file_json), true);
        }
        else {
            throw new \Exception("Fixture yaml file does not exist: " . $fixture_file);
        }

        $table_name = $fixture['table_name'];
        $single_row =  (isset($fixture['attributes'])) ? $fixture['attributes'] : false;
        $rows = (isset($fixture['records'])) ? $fixture['records'] : array();
        if ($single_row) {
            array_push($rows, $single_row);
        }

        //Map the column names for $table to an array
        $columns = __($this->get_table_attributes($fixture['table_name']))->map(function($column) {
            return $column['column_name'];
        });

        
        foreach ($rows as $attributes) {
            foreach($attributes as $k => $v){
                if (!is_string($k)) {
                    unset($attributes[$k]);
                }

                if (preg_match("/\@unique\@/", $v) !== 0) {
                    $attributes[$k] = str_replace("@unique@", uniqid(), $v);
                }
            }

            //iterate through the attributes to check if the column exists
            __($attributes)->each(function($value, $column) use ($columns, $table_name) {
                if(!__()->includ($columns, $column)) throw new \Exception( "Column $column does not exist for table $table_name");
            });  

            //override $attributes with $added_attributes
            foreach($added_attributes as $key => $value) {
                if(array_key_exists($key, $attributes)) {
                    $attributes[$key] = $value;
                }
            }  

            //get the column names and values for the query
            $column_names = __($attributes)->map(function($value, $column) { return $column; });
            $parameter_names = __($attributes)->map(function($value, $column) { return ":" . $column; });
           
            $query = "INSERT INTO $table_name (" . implode(",", $column_names) . ") VALUES (" . implode(",", $parameter_names) . ") ";

            $this->run_query($query, false, $attributes);
            usleep(20);
        }
    }

    protected function get_table_attributes($table_name) {
        $query = "SELECT COLUMN_NAME FROM `information_schema`.COLUMNS
WHERE TABLE_SCHEMA=:db_name AND TABLE_NAME=:table";
        $results = $this->run_query($query, true, array('db_name' => Config::get('database'), 'table' => $table_name));
        return $results;
    }
}

?>
