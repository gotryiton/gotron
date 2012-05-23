<?php

namespace Gotron\View;

use html2text,
    Gotron\Exception;

/**
 * View class used to generate email
 *
 * @package Framework
 */
class EmailView extends AbstractView{

    public $text_content = null;

	public $content = null;

	public $subject = null;

    protected $data = array();

    public function __construct($email) {

        $this->data['showImage'] = false;
		$this->data['emailGraphicType'] = 'blank';

        foreach($email->data as $key => $value) {
            $this->data[$key] = $value;
        }

        parent::__construct(file_join($email->view_path, $email->type . ".php"), false);
    }

    /**
     * Generates the view
     *
     * @return string
     */
    public function generate(array $parameters, $injected_view = null)
    {
        if (is_file($this->view_path)) {
            extract($parameters);
			ob_start();
			include $this->view_path;
			$this->content = ob_get_clean();
            
            if(!empty($subject)) {
		        $this->subject = $subject;
            }

		}
        else {
            throw new Exception("Cannot find view {$this->view_path}");
        }
        return $this->content;
    }

    /**
     * Adds headers to the views header array
     *
     * @return void
     */
    public function get_headers() {
		return $this->headers;
    }

    public function get_subject()
	{
	    if(is_null($this->subject)){
	        if(is_null($this->content))
	            $this->generate($this->data);

	        if(!is_null($this->subject))
	            return $this->subject;
	        else
	            return false;
	    }
	    else{
	        return $this->subject;
	    }
	}

    /**
     * Gets the HTML version of the view
     *
     * @return string
     */
    public function html_content()
	{
	    if(is_null($this->content)) {
	        return $this->generate($this->data);
        }
        else {
            return $this->content;
        }
	}

    /**
     * Gets the text version of the view
     *
     * @return string
     */
    public function text_content(){
	    if(is_null($this->text_content)) {
	        if(is_null($this->content)) {
	            $this->generate($this->data);
	        }
	        return $this->get_text();
	    }
	    else{
	        return $this->text_content;
	    }
	}

    /**
     * Pulls out the text content in the view
     *
     * @return void
     */
    private function get_text(){
		$html = $this->content;
		
		$h2t = new html2text($html);
		
		// Simply call the get_text() method for the class to convert 
		// the HTML to the plain text. Store it into the variable. 
		$this->text_content = $h2t->get_text();
		
		return $this->text_content;

	}

}

?>