<?php 
    /**
     * @brief zbxe 마이그레이션 index 파일
     * zbxe의 마이그레이션은 경로를 입력받고 회원 또는 게시판 ID를 선택받아 xml 파일을 출력하는 순서로 동작
     **/

    include "./tpl/header.php"; 
?>
    <form action="./module_list.php" method="post">
        <div class="title">Step 1. 경로 입력</div>
        <div class="desc">
            zbxe 가 설치된 경로를 입력해주세요.<br />
            예1) /home/아이디/public_html/zbxe<br />
            예2) ../zbxe<br />
        </div>

        <div class="content">
            <div class="header">설치 경로</div>
            <div class="tail"><input type="text" name="path" class="input_text" value="" /></div>
            <div class="tail"><input type="submit" class="input_submit"value="next" /></div>
        </div>
    </form>

<?php 
    include "./tpl/footer.php"; 
?>
