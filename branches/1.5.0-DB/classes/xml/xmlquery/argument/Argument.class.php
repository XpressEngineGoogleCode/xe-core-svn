<?php 

	class Argument {
		var $value;
		var $name;
		var $type;
		
		var $isValid;
		var $errorMessage;
		
		function Argument($name, $value){
			$this->name = $name;
			
			if($this->type !== 'date'){
				$dbParser = XmlQueryParser::getDBParser();
				$this->value = $dbParser->escapeStringValue($value);
			}
			else
				$this->value = $value;
			
			$this->isValid = true;
		}
		
		function getName(){
			return $this->name;
		}
		
		function getValue(){
			$value = $this->escapeValue($this->value);
			return $this->toString($value);
		}
		
		function getUnescapedValue(){
			return $this->toString($this->value);
		}
		
		function toString($value){
			if(is_array($value)) return implode(',', $value);
			return $value;			
		}
		
		function escapeValue($value){
			if(in_array($this->type, array('date', 'varchar', 'char','text', 'bigtext'))){
				if(!is_array($value))
					$value = '\''.$value.'\'';
				else {
					$total = count($value);
					for($i = 0; $i < $total; $i++)
						$value[$i] = '\''.$value[$i].'\'';
				}
			}			
			return $value;
		}
		
		function getType(){
			return $this->type;
		}
		
		function isValid(){
			return $this->isValid;
		}
		
		function getErrorMessage(){
			return $this->errorMessage;
		}
		
		function ensureDefaultValue($default_value){
			if(!isset($this->value)) 
				$this->value = $default_value;
		}
		
		function setColumnType($column_type){
			if(!isset($this->value)) return;
			if($column_type === '') return;
			
			$this->type = $column_type;
			
			//if($column_type === '') $column_type = 'varchar';

		}
		
		function checkFilter($filter_type){
			if(isset($this->value) && $this->value != ''){
				$val = $this->value;
				$key = $this->name;
			    switch($filter_type) {
	                case 'email' :
	                case 'email_address' :
	                        if(!preg_match('/^[_0-9a-z-]+(\.[_0-9a-z-]+)*@[0-9a-z-]+(\.[0-9a-z-]+)*$/is', $val)) { 
	                        	$this->isValid = false;
	                        	$this->errorMessage = new Object(-1, sprintf($lang->filter->invalid_email, $lang->{$key} ? $lang->{$key} : $key));
	                        }
	                        break;
	                case 'homepage' :
	                        if(!preg_match('/^(http|https)+(:\/\/)+[0-9a-z_-]+\.[^ ]+$/is', $val)) {
	                        	$this->isValid = false;
	                        	$this->errorMessage = new Object(-1, sprintf($lang->filter->invalid_homepage, $lang->{$key} ? $lang->{$key} : $key));
	                        }
	                    	break;
	                case 'userid' :
	                case 'user_id' :
	                        if(!preg_match('/^[a-zA-Z]+([_0-9a-zA-Z]+)*$/is', $val)) {
	                        	$this->isValid = false;
	                        	$this->errorMessage = new Object(-1, sprintf($lang->filter->invalid_userid, $lang->{$key} ? $lang->{$key} : $key));
	                        }
	                    break;
	                case 'number' :
	                case 'numbers' :
							if(is_array($val)) $val = join(',', $val);
	                        if(!preg_match('/^(-?)[0-9]+(,\-?[0-9]+)*$/is', $val)){ 
	                        	$this->isValid = false;
	                        	$this->errorMessage = new Object(-1, sprintf($lang->filter->invalid_number, $lang->{$key} ? $lang->{$key} : $key));
	                        }
	                    break;
	                case 'alpha' :
	                        if(!preg_match('/^[a-z]+$/is', $val)) {
	                        	$this->isValid = false;
	                        	$this->errorMessage = new Object(-1, sprintf($lang->filter->invalid_alpha, $lang->{$key} ? $lang->{$key} : $key));
	                        }
	                    break;
	                case 'alpha_number' :
	                        if(!preg_match('/^[0-9a-z]+$/is', $val)) {
	                        	$this->isValid = false;
	                        	$this->errorMessage = new Object(-1, sprintf($lang->filter->invalid_alpha_number, $lang->{$key} ? $lang->{$key} : $key));
	                        }
	                    break;
            	}				
			}
		}
	
		function checkMaxLength($length){
			if($this->value && (strlen($this->value) > $length)){
				$this->isValid = false;
				$key = $this->name;
				$this->errorMessage = new Object(-1, $lang->filter->outofrange, $lang->{$key} ? $lang->{$key} : $key);
			}
		}
		
		function checkMinLength($length){
			if($this->value && (strlen($this->value) > $length)){
				$this->isValid = false;
				$key = $this->name;
				$this->errorMessage = new Object(-1, $lang->filter->outofrange, $lang->{$key} ? $lang->{$key} : $key);
			}
		}		
		
		function checkNotNull(){
			if(!isset($this->value)){
				$this->isValid = false;
				$key = $this->name;
				$this->errorMessage = new Object(-1, $lang->filter->isnull, $lang->{$key} ? $lang->{$key} : $key);
			}
		}
	}


?>