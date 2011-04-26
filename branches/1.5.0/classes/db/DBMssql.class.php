<?php

    /**
     * @class DBMSSQL
     * @author NHN (developers@xpressengine.com)
     * @brief Modified to use MSSQL driver by sol (sol@ngleader.com)
     * @version 0.1
     **/

    class DBMssql extends DB {

        /**
         * information to connect to DB
         **/
		var $conn		= NULL;
        var $database	= NULL; ///< database
        var $prefix		= 'xe'; // / <prefix of XE tables(One more XE can be installed on a single DB)
		var $param		= array();
		var $comment_syntax = '/* %s */';
        
        /**
         * @brief column type used in mssql
         *
         * column_type should be replaced for each DBMS's type
         * becasue it uses commonly defined type in the schema/query xml
         **/
        var $column_type = array(
            'bignumber' => 'bigint',
            'number' => 'int',
            'varchar' => 'varchar',
            'char' => 'char',
            'text' => 'text',
            'bigtext' => 'text',
            'date' => 'varchar(14)',
            'float' => 'float',
        );

        /**
         * @brief constructor
         **/
        function DBMssql() {
            $this->_setDBInfo();
            $this->_connect();
        }
		
		/**
		 * @brief create an instance of this class
		 */
		function create()
		{
			return new DBMssql;
		}

        /**
         * @brief Return if installable
         **/
        function isSupported() {
            if (!extension_loaded("sqlsrv")) return false;
            return true;
        }

        /**
         * @brief DB settings and connect/close
         **/
        function _setDBInfo() {
            $db_info = Context::getDBInfo();
            $this->hostname = $db_info->db_hostname;
            $this->port = $db_info->db_port;
            $this->userid   = $db_info->db_userid;
            $this->password   = $db_info->db_password;
            $this->database = $db_info->db_database;
            $this->prefix = $db_info->db_table_prefix;
			
			if(!substr($this->prefix,-1)!='_') $this->prefix .= '_';
        }

        /**
         * @brief DB Connection
         **/
        function _connect() {
            // ignore if db information not exists
            if(!$this->hostname || !$this->database) return;

			//sqlsrv_configure( 'WarningsReturnAsErrors', 0 );
			//sqlsrv_configure( 'LogSeverity', SQLSRV_LOG_SEVERITY_ALL );
			//sqlsrv_configure( 'LogSubsystems', SQLSRV_LOG_SYSTEM_ALL );

			$this->conn = sqlsrv_connect( $this->hostname, 
											array( 'Database' => $this->database,'UID'=>$this->userid,'PWD'=>$this->password ));

											
// Check connections
			
		    if($this->conn){
				$this->is_connected = true;
				$this->password = md5($this->password);
			}else{
				$this->is_connected = false;
			}
        }

        /**
         * @brief DB disconnect
         **/
        function close() {
            if($this->is_connected == false) return;
			
            $this->commit();
			sqlsrv_close($this->conn);
			$this->conn = null;
        }

        /**
         * @brief handles quatation of the string variables from the query
         **/
        function addQuotes($string) {
            if(version_compare(PHP_VERSION, "5.9.0", "<") && get_magic_quotes_gpc()) $string = stripslashes(str_replace("\\","\\\\",$string));
            //if(!is_numeric($string)) $string = str_replace("'","''",$string);
			
            return $string;
        }

        /**
         * @brief Begin transaction
         **/
        function begin() {
            if($this->is_connected == false || $this->transaction_started) return;
			if(sqlsrv_begin_transaction( $this->conn ) === false) return;
			
            $this->transaction_started = true;
        }

        /**
         * @brief Rollback
         **/
        function rollback() {
            if($this->is_connected == false || !$this->transaction_started) return;
            
			$this->transaction_started = false;
            sqlsrv_rollback( $this->conn );
        }

        /**
         * @brief Commit
         **/
        function commit($force = false) {
            if(!$force && ($this->is_connected == false || !$this->transaction_started)) return;
			
            $this->transaction_started = false;	
            sqlsrv_commit( $this->conn );
        }

        /**
         * @brief : executing the query and fetching the result
         *
         * query: run a query and return the result\n
         * fetch: NULL if no value returned \n
         *        array object if rows returned \n
         *        object if a row returned \n
         *        return\n
         **/
        function _query($query) {
			if($this->is_connected == false || !$query) return;

			$_param = array();
			
			if(count($this->param)){
				foreach($this->param as $k => $o){
					if($o['type'] == 'number'){
						$_param[] = &$o['value'];
					}else{
						$_param[] = array(&$o['value'], SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STRING('utf-8'));
					}
				}	
			}
			
            // Notify to start a query execution
            $this->actStart($query);
			
            // Run the query statement
			$result = false;
			if(count($_param)){
				$result = @sqlsrv_query($this->conn, $query, $_param);
			}else{
				$result = @sqlsrv_query($this->conn, $query);
			}
// Error Check
			
			if(!$result) $this->setError(print_r(sqlsrv_errors(),true));
						
            // Notify to complete a query execution
            $this->actFinish();
			$this->param = array();

			return $result;
        }

        /**
         * @brief Fetch results
         **/
        function _fetch($result) {
			if(!$this->isConnected() || $this->isError() || !$result) return;
			
			$c = sqlsrv_num_fields($result);
			$m = null;
			$output = array();
			
			while(sqlsrv_fetch($result)){
				if(!$m) $m = sqlsrv_field_metadata($result);
				unset($row);
				for($i=0;$i<$c;$i++){
					$row->{$m[$i]['Name']} = sqlsrv_get_field( $result, $i, SQLSRV_PHPTYPE_STRING( 'utf-8' )); 
				}
				$output[] = $row;
			}

            if(count($output)==1) return $output[0];
            return $output;

        }

        /**
         * @brief Return sequence value incremented by 1(auto_increment is usd in the sequence table only)
         **/
        function getNextSequence() {
            $query = sprintf("insert into %ssequence (seq) values (ident_incr('%ssequence'))", $this->prefix, $this->prefix);
			$this->_query($query);
			
            $query = sprintf("select ident_current('%ssequence')+1 as sequence", $this->prefix);
            $result = $this->_query($query);
            $tmp = $this->_fetch($result);

			
            return $tmp->sequence;
        }

        /**
         * @brief Return if a table already exists
         **/
        function isTableExists($target_name) {
            $query = sprintf("select name from sysobjects where name = '%s%s' and xtype='U'", $this->prefix, $this->addQuotes($target_name));
            $result = $this->_query($query);			
            $tmp = $this->_fetch($result);
			
            if(!$tmp) return false;
            return true;
        }

        /**
         * @brief Add a column to a table
         **/
        function addColumn($table_name, $column_name, $type='number', $size='', $default = '', $notnull=false) {
			if($this->isColumnExists($table_name, $column_name)) return;
            $type = $this->column_type[$type];
            if(strtoupper($type)=='INTEGER') $size = '';

            $query = sprintf("alter table %s%s add %s ", $this->prefix, $table_name, $column_name);
            if($size) $query .= sprintf(" %s(%s) ", $type, $size);
            else $query .= sprintf(" %s ", $type);
            if($default) $query .= sprintf(" default '%s' ", $default);
            if($notnull) $query .= " not null ";

            $this->_query($query);
        }

        /**
         * @brief Delete a column from a table
         **/
        function dropColumn($table_name, $column_name) {
			if(!$this->isColumnExists($table_name, $column_name)) return;
            $query = sprintf("alter table %s%s drop %s ", $this->prefix, $table_name, $column_name);
            $this->_query($query);
        }

        /**
         * @brief Return column information of a table
         **/
        function isColumnExists($table_name, $column_name) {
            $query = sprintf("select syscolumns.name as name from syscolumns, sysobjects where sysobjects.name = '%s%s' and sysobjects.id = syscolumns.id and syscolumns.name = '%s'", $this->prefix, $table_name, $column_name);
            $result = $this->_query($query);
            if($this->isError()) return;
            $tmp = $this->_fetch($result);
            if(!$tmp->name) return false;
            return true;
        }

        /**
         * @brief Add an index to a table
         * $target_columns = array(col1, col2)
         * $is_unique? unique : none
         **/
        function addIndex($table_name, $index_name, $target_columns, $is_unique = false) {
			if($this->isIndexExists($table_name, $index_name)) return;
            if(!is_array($target_columns)) $target_columns = array($target_columns);

            $query = sprintf("create %s index %s on %s%s (%s)", $is_unique?'unique':'', $index_name, $this->prefix, $table_name, implode(',',$target_columns));
            $this->_query($query);
        }

        /**
         * @brief Drop an index from a table
         **/
        function dropIndex($table_name, $index_name, $is_unique = false) {
			if(!$this->isIndexExists($table_name, $index_name)) return;
            $query = sprintf("drop index %s%s.%s", $this->prefix, $table_name, $index_name);
            $this->_query($query);
        }

        /**
         * @brief Return index information of a table
         **/
        function isIndexExists($table_name, $index_name) {
            $query = sprintf("select sysindexes.name as name from sysindexes, sysobjects where sysobjects.name = '%s%s' and sysobjects.id = sysindexes.id and sysindexes.name = '%s'", $this->prefix, $table_name, $index_name);

            $result = $this->_query($query);
            if($this->isError()) return;
            $tmp = $this->_fetch($result);

            if(!$tmp->name) return false;
            return true;
        }

        /**
         * @brief Create a table by using xml file
         **/
        function createTableByXml($xml_doc) {
            return $this->_createTable($xml_doc);
        }

        /**
         * @brief Create a table by using xml file
         **/
        function createTableByXmlFile($file_name) {
            if(!file_exists($file_name)) return;
            // read xml file
            $buff = FileHandler::readFile($file_name);
            return $this->_createTable($buff);
        }

        /**
         * @brief generate a query statement to create a table by using schema xml
         *
         * type : number, varchar, text, char, date, \n
         * opt : notnull, default, size\n
         * index : primary key, index, unique\n
         **/
        function _createTable($xml_doc) {
            // xml parsing
            $oXml = new XmlParser();
            $xml_obj = $oXml->parse($xml_doc);
            // Create a table schema
            $table_name = $xml_obj->table->attrs->name;
            if($this->isTableExists($table_name)) return;

            if($table_name == 'sequence') {
                $table_name = $this->prefix.$table_name;
                $query = sprintf('create table %s ( sequence int identity(1,1), seq int )', $table_name);
                return $this->_query($query);
            } else {
                $table_name = $this->prefix.$table_name;

                if(!is_array($xml_obj->table->column)) $columns[] = $xml_obj->table->column;
                else $columns = $xml_obj->table->column;

                foreach($columns as $column) {
                    $name = $column->attrs->name;
                    $type = $column->attrs->type;
                    $size = $column->attrs->size;
                    $notnull = $column->attrs->notnull;
                    $primary_key = $column->attrs->primary_key;
                    $index = $column->attrs->index;
                    $unique = $column->attrs->unique;
                    $default = $column->attrs->default;
                    $auto_increment = $column->attrs->auto_increment;

                    $column_schema[] = sprintf('[%s] %s%s %s %s %s %s',
                    $name,
                    $this->column_type[$type],
                    !in_array($type,array('number','text'))&&$size?'('.$size.')':'',
                    $primary_key?'primary key':'',
                    isset($default)?"default '".$default."'":'',
                    $notnull?'not null':'null',
                    $auto_increment?'identity(1,1)':''
                    );

                    if($unique) $unique_list[$unique][] = $name;
                    else if($index) $index_list[$index][] = $name;
                }
				
                $schema = sprintf('create table [%s] (xe_seq int identity(1,1),%s%s)', $this->addQuotes($table_name), "\n", implode($column_schema,",\n"));
                $output = $this->_query($schema);
                if(!$output) return false;
				
                if(count($unique_list)) {
                    foreach($unique_list as $key => $val) {
                        $query = sprintf("create unique index %s on %s (%s);", $key, $table_name, '['.implode('],[',$val).']');
                        $this->_query($query);
                    }
                }

                if(count($index_list)) {
                    foreach($index_list as $key => $val) {
                        $query = sprintf("create index %s on %s (%s);", $key, $table_name, '['.implode('],[',$val).']');
                        $this->_query($query);
                    }
                }
				return true;
            }
        }

        /**
         * @brief Return conditional clause
         **/
        function getCondition($output) {
            if(!$output->conditions) return;
            $condition = $this->_getCondition($output->conditions,$output->column_type);
            if($condition) $condition = ' where '.$condition;
            return $condition;
        }

        function getLeftCondition($conditions,$column_type){
            return $this->_getCondition($conditions,$column_type);
        }


        function _getCondition($conditions,$column_type) {
            $condition = '';

            foreach($conditions as $val) {
                $sub_condition = '';
                foreach($val['condition'] as $v) {
                    if(!isset($v['value'])) continue;
                    if($v['value'] === '') continue;
                    if(!in_array(gettype($v['value']), array('string', 'integer', 'double'))) continue;

                    $name = $v['column'];
					if(preg_match('/^substr\(/i',$name)) $name = preg_replace('/^substr\(/i','substring(',$name);
                    $operation = $v['operation'];
                    $value = $v['value'];

                    $type = $this->getColumnType($column_type,$name);
                    $pipe = $v['pipe'];

                    $value = $this->getConditionValue($name, $value, $operation, $type, $column_type);
                    if(!$value) $value = $v['value'];
                    $str = $this->getConditionPart($name, $value, $operation);
                    if($sub_condition) $sub_condition .= ' '.$pipe.' ';
                    $sub_condition .=  $str;
                }
                if($sub_condition) {
                    if($condition && $val['pipe']) $condition .= ' '.$val['pipe'].' ';
                    $condition .= '('.$sub_condition.')';
                }
            }
            return $condition;
        }

		
		function getConditionValue($name, $value, $operation, $type, $column_type) {
			
			if($type == 'number') {
                if(strpos($value,',')===false && strpos($value,'(')===false){
				
					if(is_integer($value)){
						$this->param[] = array('type'=>'number','value'=>(int)$value);
						return '?';
					}else{
						return $value;
					}
				}
            }

            if(strpos($name,'.')!==false&&strpos($value,'.')!==false) {
                list($table_name, $column_name) = explode('.',$value);
                if($column_type[$column_name]){
					return $value;
				}
            }
	
            switch($operation) {
                case 'like_prefix' :
						$value = preg_replace('/(^\'|\'$){1}/','',$value);
						$this->param[] = array('type'=>$column_type[$name],'value'=>$value);
							
                        $value = "? + '%'";
                    break;
                case 'like_tail' :
						$value = preg_replace('/(^\'|\'$){1}/','',$value);
						$this->param[] = array('type'=>$column_type[$name],'value'=>$value);				
						
                        $value = "'%' + ?";
                    break;
                case 'like' :
						$value = preg_replace('/(^\'|\'$){1}/','',$value);
						$this->param[] = array('type'=>$column_type[$name],'value'=>$value);				
				
                        $value = "'%' + ? + '%'";
                    break;
                case 'notin' :
						preg_match_all('/,?\'([^\']*)\'/',$value,$match);
						$val = array();
						foreach($match[1] as $k => $v){
							$this->param[] = array('type'=>$column_type[$name],'value'=>trim($v));				
							$val[] ='?';
						}
                        $value = join(',',$val);
                    break;
                case 'in' :
                        preg_match_all('/,?\'([^\']*)\'/',$value,$match);
						$val = array();
						foreach($match[1] as $k => $v){
							$this->param[] = array('type'=>$column_type[$name],'value'=>trim($v));				
							$val[] ='?';
						}
                        $value = join(',',$val);
                    break;
				default:
						$value = preg_replace('/(^\'|\'$){1}/','',$value);
						$this->param[] = array('type'=>$column_type[$name],'value'=>$value);				
						$value = '?';
					break;
            }

            return $value;
        }
		
        /**
         * @brief Handle the insertAct
         **/
        function _executeInsertAct($output) {
		
            // List tables
            foreach($output->tables as $key => $val) {
                $table_list[] = '['.$this->prefix.$val.']';
            }
            // List columns
            foreach($output->columns as $key => $val) {
                $name = $val['name'];
                $value = $val['value'];
				
                if($output->column_type[$name]!='number') {
                    $value = $this->addQuotes($value);
					if(!$value) $value = '';
				} elseif(is_numeric($value)){
					if(!$value) $value = '';
					$value = (int)$value;
				} elseif(!$value){
					$value = '';
				}
				
                $column_list[] = '['.$name.']';
				$value_list[] = '?';
				
                $this->param[] = array('type'=>$output->column_type[$name], 'value'=>$value);
            }

            $query = sprintf("insert into %s (%s) values (%s);", implode(',',$table_list), implode(',',$column_list), implode(',', $value_list));

            return $this->_query($query);
        }

        /**
         * @brief Handle updateAct
         **/
        function _executeUpdateAct($output) {
            // List tables
            foreach($output->tables as $key => $val) {
                $table_list[] = '['.$this->prefix.$val.']';
            }
		
// List columns
		    
            foreach($output->columns as $key => $val) {
                if(!isset($val['value'])) continue;
				
                $name = $val['name'];
                $value = $val['value'];
                if(strpos($name,'.')!==false&&strpos($value,'.')!==false){
					$column_list[] = $name.' = '.$value;
				} else {
                    if($output->column_type[$name]!='number'){
						$value = $this->addQuotes($value);
						if(!$value) $value = '';
						
						$this->param[] = array('type'=>$output->column_type[$name], 'value'=>$value);
						$column_list[] = sprintf("[%s] = ?",  $name);
                    }elseif(!$value || is_numeric($value)){
						$value = (int)$value;
						
						$this->param[] = array('type'=>$output->column_type[$name], 'value'=>$value);
						$column_list[] = sprintf("[%s] = ?",  $name);
					}else{
						if(!$value) $value = '';
						$column_list[] = sprintf("[%s] = %s",  $name, $value);
					}

					
                }
            }
            // List the conditional clause
            $condition = $this->getCondition($output);

            $query = sprintf("update %s set %s %s", implode(',',$table_list), implode(',',$column_list), $condition);

            return $this->_query($query);
        }

        /**
         * @brief Handle deleteAct
         **/
        function _executeDeleteAct($output) {
            // List tables
            foreach($output->tables as $key => $val) {
                $table_list[] = '['.$this->prefix.$val.']';
            }
            // List the conditional clause
            $condition = $this->getCondition($output);

            $query = sprintf("delete from %s %s", implode(',',$table_list), $condition);

            return $this->_query($query);
        }

        /**
         * @brief Handle selectAct
         *
         * In order to get a list of pages easily when selecting \n
         * it supports a method as navigation
         **/
        function _executeSelectAct($output) {
            // List tables
            $table_list = array();
            foreach($output->tables as $key => $val) {
                $table_list[] = '['.$this->prefix.$val.'] as '.$key;
            }

            $left_join = array();
            // why???
            $left_tables= (array)$output->left_tables;

            foreach($left_tables as $key => $val) {
                $condition = $this->_getCondition($output->left_conditions[$key],$output->column_type);
                if($condition){
                    $left_join[] = $val . ' ['.$this->prefix.$output->_tables[$key].'] as '.$key  . ' on (' . $condition . ')';
                }
            }

            $click_count = array();
            if(!$output->columns){
				$output->columns = array(array('name'=>'*'));
			}

			$column_list = array();
			foreach($output->columns as $key => $val) {
				$name = $val['name'];
				if(preg_match('/^substr\(/i',$name)) $name = preg_replace('/^substr\(/i','substring(',$name);
				$alias = $val['alias'];
				if($val['click_count']) $click_count[] = $val['name'];

				if(substr($name,-1) == '*') {
					$column_list[] = $name;
				} elseif(strpos($name,'.')===false && strpos($name,'(')===false) {
					if($alias) $column_list[$alias] = sprintf('[%s] as [%s]', $name, $alias);
					else $column_list[] = sprintf('[%s]',$name);
				} else {
					if($alias) $column_list[$alias] = sprintf('%s as [%s]', $name, $alias);
					else $column_list[] = sprintf('%s',$name);
				}
			}
			$columns = implode(',',$column_list);

            $condition = $this->getCondition($output);
		
			$output->column_list = $column_list;
            if($output->list_count && $output->page) return $this->_getNavigationData($table_list, $columns, $left_join, $condition, $output);
            // Add a condition to use an index when sorting in order by list_order, update_order
            if($output->order) {
                $conditions = $this->getConditionList($output);
                if(!in_array('list_order', $conditions) && !in_array('update_order', $conditions)) {
                    foreach($output->order as $key => $val) {
                        $col = $val[0];
                        if(!in_array($col, array('list_order','update_order'))) continue;
                        if($condition) $condition .= sprintf(' and %s < 2100000000 ', $col);
                        else $condition = sprintf(' where %s < 2100000000 ', $col);
                    }
                }
            }

			if(count($output->groups)){
				foreach($output->groups as $k => $v ){
					if(preg_match('/^substr\(/i',$v)) $output->groups[$k] = preg_replace('/^substr\(/i','substring(',$v);
					if($column_list[$v]) $output->arg_columns[] = $column_list[$v];
				}
				$groupby_query = sprintf(' group by %s', implode(',',$output->groups));
			}

            if($output->order && !preg_match('/count\(\*\)/i',$columns) ) {
                foreach($output->order as $key => $val) {
					if(preg_match('/^substr\(/i',$val[0])) $name = preg_replace('/^substr\(/i','substring(',$val[0]);
                    $index_list[] = sprintf('%s %s', $val[0], $val[1]);
					if(count($output->arg_columns) && $column_list[$val[0]]) $output->arg_columns[] = $column_list[$val[0]];
                }
                if(count($index_list)) $orderby_query = ' order by '.implode(',',$index_list);
            }

			if(count($output->arg_columns))
			{
				$columns = array();
				foreach($output->arg_columns as $col){
					unset($tmpCol);
					$tmpCol = explode('.', $col);
					if(isset($tmpCol[1])) $col = $tmpCol[1];

					if(strpos($col,'[')===false && strpos($col,' ')==false) $col = '['.$col.']'; 
					if(isset($tmpCol[1])) $col = $tmpCol[0].'.'.$col;

					$columns[] = $col;
				}
				
				$columns = join(',',$columns);
			}

            $query = sprintf("%s from %s %s %s %s", $columns, implode(',',$table_list),implode(' ',$left_join), $condition, $groupby_query.$orderby_query);
            // Apply when using list_count
            if($output->list_count['value']) $query = sprintf('select top %d %s', $output->list_count['value'], $query);
			else $query = "select ".$query;

			$query .= (__DEBUG_QUERY__&1 && $output->query_id)?sprintf(' '.$this->comment_syntax,$this->query_id):'';
            $result = $this->_query($query);
            if($this->isError()) return;

            if(count($click_count)>0 && count($output->conditions)>0){
                $_query = '';
                foreach($click_count as $k => $c) $_query .= sprintf(',%s=%s+1 ',$c,$c);
                $_query = sprintf('update %s set %s %s',implode(',',$table_list), substr($_query,1),  $condition);
                $this->_query($_query);
            }

            $data = $this->_fetch($result);

            $buff = new Object();
            $buff->data = $data;
            return $buff;
        }

        /**
         * @brief Paging is handled if navigation information exists in the query xml
         *
         * It is quite convenient although its structure is not good at all .. -_-;
         **/
        function _getNavigationData($table_list, $columns, $left_join, $condition, $output) {
            require_once(_XE_PATH_.'classes/page/PageHandler.class.php');

			$column_list = $output->column_list;

            // Get a total count
			if(count($output->groups)){
				foreach($output->groups as $k => $v ){
					if(preg_match('/^substr\(/i',$v)) $output->groups[$k] = preg_replace('/^substr\(/i','substring(',$v);
					if($column_list[$v]) $output->arg_columns[] = $column_list[$v];
				}
				$count_condition = sprintf('%s group by %s', $condition, implode(', ', $output->groups));
			}else{
				$count_condition = $condition;
			}
			
			$count_query = sprintf("select count(*) as count from %s %s %s", implode(', ', $table_list), implode(' ', $left_join), $count_condition);
			if (count($output->groups)) $count_query = sprintf('select count(*) as count from (%s) xet', $count_query);
			
			$param = $this->param;

			$count_query .= (__DEBUG_QUERY__&1 && $output->query_id)?sprintf(' '.$this->comment_syntax,$this->query_id . ' count(*)'):'';
			$result = $this->_query($count_query);
			
			$this->param = $param;
			$count_output = $this->_fetch($result);

			$total_count = (int)$count_output->count;

            $list_count = $output->list_count['value'];
            if(!$list_count) $list_count = 20;
            $page_count = $output->page_count['value'];
            if(!$page_count) $page_count = 10;
            $page = $output->page['value'];
            if(!$page) $page = 1;
            // Get a total page
            if($total_count) $total_page = (int)( ($total_count-1) / $list_count) + 1;
            else $total_page = 1;
            // Check Page variables
            if($page > $total_page) $page = $total_page;
            $start_count = ($page-1)*$list_count;
            // Add a condition to use an index when sorting in order by list_order, update_order
            $conditions = $this->getConditionList($output);
            if($output->order) {
                if(!in_array('list_order', $conditions) && !in_array('update_order', $conditions)) {
                    foreach($output->order as $key => $val) {
                        $col = $val[0];
                        if(!in_array($col, array('list_order','update_order'))) continue;
                        if($condition) $condition .= sprintf(' and %s < 2100000000 ', $col);
                        else $condition = sprintf(' %s < 2100000000 ', $col);
                    }
                }
            }
			
            // Add group by clause
			if(count($output->groups)){
				foreach($output->groups as $k => $v ){
					if(preg_match('/^substr\(/i',$v)) $output->groups[$k] = preg_replace('/^substr\(/i','substring(',$v);
					if($column_list[$v]) $output->arg_columns[] = $column_list[$v];
				}

				$group = sprintf('group by %s', implode(',',$output->groups));
			}
			
            // Add order by clause
            $order_targets = array();
            if($output->order) {
                foreach($output->order as $key => $val) {
					if(preg_match('/^substr\(/i',$val[0])) $name = preg_replace('/^substr\(/i','substring(',$val[0]);
                    $order_targets[$val[0]] = $val[1];
                    $index_list[] = sprintf('%s %s', $val[0], $val[1]);
					if(count($output->arg_columns) && $column_list[$val[0]]) $output->arg_columns[] = $column_list[$val[0]];
                }
                if(count($index_list)) $order .= 'order by '.implode(',',$index_list);
            }
            if(!count($order_targets)) {
                if(in_array('list_order',$conditions)) $order_targets['list_order'] = 'asc';
                else $order_targets['xe_seq'] = 'desc';
            }

			if(count($output->arg_columns))
			{
				$columns = array();
				foreach($output->arg_columns as $col){
					unset($tmpCol);
					$tmpCol = explode('.', $col);
					if(isset($tmpCol[1])) $col = $tmpCol[1];

					if(strpos($col,'[')===false && strpos($col,' ')==false) $col = '['.$col.']'; 
					if(isset($tmpCol[1])) $col = $tmpCol[0].'.'.$col;

					$columns[] = $col;
				}
				
				$columns = join(',',$columns);
			}

            if($start_count<1) {
                $query = sprintf('select top %d %s from %s %s %s %s %s', $list_count, $columns, implode(',',$table_list), implode(' ',$left_join), $condition, $group, $order);

            } else {
                foreach($order_targets as $k => $v) {
					$first_columns[] = sprintf('%s(%s) as %s', $v=='asc'?'max':'min', $k, $k);
					$first_sub_columns[] = $k;
                }
				
				// Fetch values to sort
				$param = $this->param;
				$first_query = sprintf("select %s from (select top %d %s from %s %s %s %s %s) xet", implode(',',$first_columns),  $start_count, implode(',',$first_sub_columns), implode(',',$table_list), implode(' ',$left_join), $condition, $group, $order);
				$result = $this->_query($first_query);
				$this->param = $param;
				$tmp = $this->_fetch($result);
				

				
				// Re-execute a query by using fetched values 
				$sub_cond = array();
                foreach($order_targets as $k => $v) {
                    $sub_cond[] = sprintf("%s %s '%s'", $k, $v=='asc'?'>':'<', $tmp->{$k});
				}
				$sub_condition = ' and( '.implode(' and ',$sub_cond).' )';
				
				if($condition) $condition .= $sub_condition;
				else $condition  = ' where '.$sub_condition;
				$query = sprintf('select top %d %s from %s %s %s %s %s', $list_count, $columns, implode(',',$table_list), implode(' ',$left_join), $condition, $group, $order);
            }

			$query .= (__DEBUG_QUERY__&1 && $output->query_id)?sprintf(' '.$this->comment_syntax,$this->query_id):'';
            $result = $this->_query($query);
			
            if($this->isError()) {
                $buff = new Object();
                $buff->total_count = 0;
                $buff->total_page = 0;
                $buff->page = 1;
                $buff->data = array();

                $buff->page_navigation = new PageHandler($total_count, $total_page, $page, $page_count);
                return $buff;
            }

			$virtual_no = $total_count - ($page-1)*$list_count;
			
			$output = $this->_fetch($result);
			if(!is_array($output)) $output = array($output);

            foreach($output as $k => $v) {
                $data[$virtual_no--] = $v;
            }
			
            $buff = new Object();
            $buff->total_count = $total_count;
            $buff->total_page = $total_page;
            $buff->page = $page;
            $buff->data = $data;

            $buff->page_navigation = new PageHandler($total_count, $total_page, $page, $page_count);
            return $buff;
        }

    }

return new DBMssql;
?>
