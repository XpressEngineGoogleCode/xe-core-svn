<?php
    /**
     * @brief 제로보드XE에서 import할 수 있는 구조의 xml data를 출력하는 마이그레이션 공용 class
     * @author zero@zeroboard.com
     **/

    class zMigration {

        var $connect;
        var $handler;

        var $errno = 0;
        var $error = null;

        var $path = null;
        var $module_type = 'member';
        var $module_id = '';

        var $filename = '';

        var $item_count = 0;

        var $source_charset = 'EUC-KR';
        var $target_charset = 'UTF-8';

        var $db_info = null;

        function zMigration($path, $module_type = 'member', $module_id = '', $source_charset = 'EUC-KR', $target_charset = 'UTF-8') {
            $this->path = $path;
            $this->module_type = $module_type;
            if($this->module_type == 'module') $this->module_id = $module_id;
            $this->source_charset = $source_charset;
            $this->target_charset = $target_charset;
        }

        function setDBInfo($db_info) {
            $this->db_info = $db_info;
        }

        function setItemCount($count) {
            $this->item_count = $count;
        }

        function setFilename($filename) {
            $this->filename = $filename;
        }

        function dbConnect() {
            switch($this->db_info->db_type) {
                case 'mysql' :
                case 'mysql_innodb' :
                        $this->connect =  @mysql_connect($this->db_info->db_hostname, $this->db_info->db_userid, $this->db_info->db_password);
                        if(!mysql_error()) @mysql_select_db($this->db_info->db_database, $this->connect);
                        if(mysql_error()) return mysql_error();
                        mysql_query("set names 'utf8'");
                    break;
                case 'cubrid' :
                        $this->connect = @cubrid_connect($this->db_info->hostname, $this->db_info->port, $this->db_info->db_database, $this->db_info->userid, $this->db_info->password);
                        if(!$this->connect) return 'database connect fail';
                    break;
                case 'sqlite3_pdo' :
                        if(substr($this->db_info->db_database,0,1)!='/') $this->db_info->db_database = $this->path.'/'.$this->db_info->db_database;
                        if(!file_exists($this->db_info->db_database)) return "database file not found";
                        $this->handler = new PDO('sqlite:'.$this->db_info->db_database);
                        if(!file_exists($this->db_info->db_database) || $error) return 'permission denied to access database';
                    break;
                case 'sqlite' :
                        if(substr($this->db_info->db_database,0,1)!='/') $this->db_info->db_database = $this->path.'/'.$this->db_info->db_database;
                        if(!file_exists($this->db_info->db_database)) return "database file not found";
                        $this->connect = @sqlite_open($this->db_info->db_database, 0666, &$error);
                        if($error) return $error;
                    break;
            }
        }

        function dbClose() {
            if(!$this->connect) return;
            mysql_close($this->connect);
        }

        function query($query) {
            switch($this->db_info->db_type) {
                case 'mysql' :
                case 'mysql_innodb' :
                        return mysql_query($query);
                    break;
                case 'cubrid' :
                        return @cubrid_execute($this->connect, $query);
                    break;
                case 'sqlite3_pdo' :
                        $stmt = $this->handler->prepare($query);
                        $stmt->execute();
                        return $stmt;
                    break;
                case 'sqlite' :
                        return sqlite_query($query, $this->connect);
                    break;
            }
        }

        function fetch($result) {
            switch($this->db_info->db_type) {
                case 'mysql' :
                case 'mysql_innodb' :
                        return mysql_fetch_object($result);
                    break;
                case 'cubrid' :
                        return cubrid_fetch($result, CUBRID_OBJECT);
                    break;
                case 'sqlite3_pdo' :
                        $tmp = $result->fetch(PDO::FETCH_ASSOC);
                        foreach($tmp as $key => $val) {
                            $pos = strpos($key, '.');
                            if($pos) $key = substr($key, $pos+1);
                            $obj->{$key} = str_replace("''","'",$val);
                        }
                        return $obj;
                    break;
                case 'sqlite' :
                        $tmp = sqlite_fetch_array($result, SQLITE_ASSOC);
                        unset($obj);
                        foreach($tmp as $key => $val) {
                            $pos = strpos($key, '.');
                            if($pos) $key = substr($key, $pos+1);
                            $obj->{$key} = $val;
                        }
                        return $obj;
                    break;
            }
        }

        function printHeader() {
            if(!$this->filename) $filename = sprintf("%s%s.xml", $this->module_type=='member'?'member':'', $this->module_id?'.'.$this->module_id:'');
            else $filename = $this->filename;

            if(strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
                $filename = urlencode($filename);
                $filename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
            }

            header("Content-Type: application/octet-stream");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header("Content-Transfer-Encoding: binary");

            printf('<?xml version="1.0" encoding="utf-8" ?>%s',"\r\n");

            if($this->module_type == 'member') printf('<members count="%d" pubDate="%s">%s', $this->item_count, date("YYYYMMDDHHIISS"), "\r\n");
             else printf('<posts count="%d" id="%s" pubDate="%s">%s', $this->item_count, $this->module_id, date("YYYYMMDDHHIISS"), "\r\n");
        }

        function printFooter() { 
            if($this->module_type == 'member') print('</members>');
             else print('</posts>');
        }

        function printString($string) {
            $string = stripslashes($string);
            if($this->source_charset == 'UTF-8') print base64_encode($string);
            else print base64_encode(iconv($this->source_charset, $this->target_charset, $string));
        }

        function printBinary($filename) {
            $filesize = filesize($filename);
            if($filesize<1) return;

            $fp = fopen($filename,"r");
            if($fp) {
                while(!feof($fp)) {
                    $buff .= fgets($fp, 1024);
                    if(strlen($buff)>1024*512) {
                        print "\r\n<buff>"; print base64_encode($buff); print "</buff>";
                        $buff = null;
                    }
                }
                if($buff) print "\r\n<buff>"; print base64_encode($buff); print "</buff>\r\n";
                fclose($fp);
            }
            return null;
        }

        function printMemberItem($obj) {
            // member 태그 시작
            print "<member>\r\n";

            // extra_vars를 제외한 변수 출력
            foreach($obj as $key => $val) {
                if($key == 'extra_vars' || !$val) continue;

                if($key == 'image_nickname' || $key == 'image_mark' || $key == 'profile_image') {
                    if(file_exists($val)) {
                        printf("<%s>", $key);
                        $this->printBinary($val);
                        printf("</%s>\r\n", $key);
                    }
                    continue;
                }

                printf("<%s>", $key); $this->printString($val); printf("</%s>\r\n", $key);
            }

            if(count($obj->extra_vars)) {
                print("<extra_vars>\r\n");
                foreach($obj->extra_vars as $key => $val) {
                    if(!$val) continue;
                    printf("<%s>", $key); $this->printString($val); printf("</%s>\r\n", $key);
                } 
                print("</extra_vars>\r\n");
            }

            // member 태그 닫음
            print "</member>\r\n";
        }

        function printCategoryItem($obj) {
            if(!count($obj)) return;

            print("<categories>\r\n");
            foreach($obj as $key => $val) {
                print "<category>";
                $this->printString($val); 
                print "</category>\r\n";
            }
            print("</categories>\r\n");
        }

        function printPostItem($sequence, $obj) {
            print "<post>\r\n";
            // extra_vars, trackbacks, comments, attaches 정보를 별도로 분리
            $extra_vars = $obj->extra_vars;
            unset($obj->extra_vars);
            $trackbacks = $obj->trackbacks;
            unset($obj->trackbacks);
            $comments = $obj->comments;
            unset($obj->comments);
            $attaches = $obj->attaches;
            unset($obj->attaches);

            // 내용을 출력
            foreach($obj as $key => $val) {
                if(!$val) continue;
                printf("<%s>", $key); $this->printString($val); printf("</%s>\r\n", $key);
            }

            // 엮인글 출력
            $trackback_count = count($trackbacks);
            if($trackback_count) {
                printf('<trackbacks count="%d">%s', $trackback_count, "\r\n");
                foreach($trackbacks as $key => $val) {
                    print "<trackback>\r\n";
                        foreach($val as $k => $v) {
                            if(!$v) continue;
                            printf("<%s>", $k); $this->printString($v); printf("</%s>\r\n", $k);
                        }
                    print "</trackback>\r\n";
                }
                print "</trackbacks>\r\n";
            }

            // 댓글 출력
            $comment_count = count($comments);
            if($comment_count) {
                printf('<comments count="%d">%s', $comment_count, "\r\n");

                foreach($comments as $key => $val) {

                    $att = null;
                    $att = $val->attaches;
                    unset($val->attaches);

                    print "<comment>\r\n";

                    foreach($val as $k => $v) {
                        if(!$v) continue;
                        printf("<%s>", $k); $this->printString($v); printf("</%s>\r\n", $k);
                    }

                    $file_count = count($att);
                    if($file_count) {
                        printf('<attaches count="%d">%s', $file_count, "\r\n");
                        foreach($att as $k=> $v) {
                            if(!file_exists($v->file)) continue;

                            print "<attach>\r\n";

                            print "<filename>"; $this->printString($v->filename); print "</filename>\r\n";
                            print "<download_count>"; $this->printString($v->download_count); print "</download_count>\r\n";
                            print "<file>"; $this->printBinary($v->file); print "</file>\r\n";

                            print "</attach>\r\n";
                        }
                        print "</attaches>\r\n";
                    }
                    print "</comment>\r\n";
                }

                print "</comments>\r\n";
            }

            // 첨부파일 출력
            $file_count = count($attaches);
            if($file_count) {
                printf('<attaches count="%d">%s', $file_count, "\r\n");
                foreach($attaches as $key => $val) {
                    if(!file_exists($val->file)) continue;

                    print "<attach>\r\n";

                    print "<filename>"; $this->printString($val->filename); print "</filename>\r\n";
                    print "<download_count>"; $this->printString($val->download_count); print "</download_count>\r\n";
                    print "<file>"; $this->printBinary($val->file); print "</file>\r\n";

                    print "</attach>\r\n";
                }
                print "</attaches>\r\n";
            }

            // 추가 변수 출력
            if(count($extra_vars)) {
                print "<extra_vars>\r\n";
                foreach($extra_vars as $key => $val) {
                    print "<key>";
                    $this->printString($val);
                    print "</key>";
                }
                print "</extra_vars>\r\n";
            }

            print "</post>\r\n";
        }
    }
?>
