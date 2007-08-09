<?php 
    include "lib.php";

    include "./tpl/header.php"; 

    if(!ereg("^module\_", $target_module)) {
        $action_file = 'export_member.php';
        $target_title = '회원정보'; 
    } else {
        $action_file = 'export_board.php';
        $target_title = sprintf('%s (%s)',  substr($target_module, 7), '게시판' );

        $hostname = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        if($port!=80) $hostname .= ":{$port}";

        preg_match("/([a-zA-Z\_]+)\.php/i", $_SERVER['PHP_SELF'], $match);
        $filename = $match[0];
        $query_url = str_replace($filename, '', $_SERVER['PHP_SELF']);

        $module_url = sprintf("http://%s%s",$hostname, $query_url);

        $query = sprintf("select * from zetyx_board_category_%s",  substr($target_module, 7));
        $result = mysql_query($query);
        while($tmp = mysql_fetch_object($result)) {
          $category_list[$tmp->no] = iconv($source_charset, $target_charset, $tmp->name)." (".$tmp->num.")";
        }
    }
?>

    <form action="./<?=$action_file?>" method="post">
    <input type="hidden" name="path" value="<?=$path?>" />
    <input type="hidden" name="charset" value="<?=$charset?>" />
    <input type="hidden" name="target_module" value="<?=$target_module?>" />

        <div class="title">Step 3. 백업할 파일 이름을 선택해주세요.</div>
        <div class="desc">백업 파일은 파일이름.xml로 저장되며 제로보드XE에서 import 가능합니다.</div>

        <div class="content">
            <div class="header">백업 대상</div>
            <div class="tail"><?=$target_title?></div>
        </div>

<?
  if(count($category_list)) {
?>
        <div class="content">
            <div class="header">카테고리</div>
            <div class="tail">
              <select name="category_srl">
              <option value="">전체</option>
<? foreach($category_list as $key => $val) {?>
            <option value="<?=$key?>"><?=$val?></option>
<? } ?>
              </select>
            </div>
        </div>
<?
  }
?>

        <div class="content">
            <div class="header">파일 이름</div>
            <div class="tail"><input type="text" class="input_text" name="filename" value="<?=eregi_replace('^module_','',$target_module)?>_<?=date("Ymd_His")?>.xml" /></div>
            <div class="tail"><input type="submit" class="input_submit" value="next" /></div>
        </div>

    </form>

<?php include "./tpl/footer.php"; ?>
