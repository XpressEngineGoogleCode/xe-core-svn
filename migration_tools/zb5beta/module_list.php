<?php 
    include "lib.php";

    // 게시판 목록 구하기
    $query = "select * from {$db_prefix}module_manager";
    $module_list_result = mysql_query($query) or die(mysql_error());
    
    include "./tpl/header.php"; 
?>

    <form action="./export.php" method="post">
    <input type="hidden" name="path" value="<?=$path?>" />

        <div class="title">Step 2. 백업할 대상을 선택해주세요. (회원정보 또는 게시판)</div>
        <div class="desc">zb5beta는 회원정보와 그외 모듈 종류를 나누어 백업하실 수 있습니다.</div>

        <div class="content">
            <input type="radio" name="target_module" value="member" id="member" />
            <label for="member">회원정보</label>
        </div>

<?
    while($module_info = mysql_fetch_object($module_list_result)) {
?>
        <div class="content">
            <input type="radio" name="target_module" value="module_<?=$module_info->module_srl?>" id="module_<?=$module_info->module_srl?>" />
            <label for="module_<?=$module_info->module_srl?>"><?=$module_info->title?></label>
        </div>
<?
    }
?>

        <div class="button_area">
            <input type="submit" value="next" class="input_submit" />
        </div>

    </form>

<?php include "./tpl/footer.php"; ?>
