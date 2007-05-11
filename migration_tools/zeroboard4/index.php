<?php include "./tpl/header.php"; ?>

<div id="db_info">
    <div class="title">Step 1. DB정보를 입력해주세요.</div>
    <form action="./" method="get">
        <ul>
            <li>
                <div class="header">db host</div>
                <div class="tail"><input type="text" name="hostname" value="localhost" /></div>
            </li>
            <li>
                <div class="header">user id</div>
                <div class="tail"><input type="text" name="user_id" value="" /></div>
            </li>
            <li>
                <div class="header">password</div>
                <div class="tail"><input type="password" name="password" value="" /></div>
            </li>
            <li>
                <div class="header">db name</div>
                <div class="tail"><input type="text" name="db_name" value="" /></div>
                <div class="tail"><input type="submit" value="next" class="button" /></div>
            </li>
        </ul>
    </form>
</div>

<div id="module_list" style="display:none">
    <div class="title">Step 2. 회원정보 또는 게시판을 선택해주세요.</div>
    <form action="./" method="get">
        <div>
            <input type="radio" name="target_module" value="member" id="member" />
            <label for="member">member</label>
            <input type="radio" name="target_module" value="module_board" id="module_board" />
            <label for="module_board">board</label>
        </div>
        <div class="submit_button">
            <input type="submit" value="next" class="button" />
        </div>
    </form>
</div>

<div id="category_list" style="display:none">
    <div class="title">Step 3. 카테고리를 선택해주세요.</div>
    <form action="./" method="get">
        <div class="category_list">
            <input type="checkbox" name="category" value="0" id="category_0" checked="true" />
            <label for="category_0">category1</label>
            <input type="checkbox" name="category" value="1" id="category_1" checked="true" />
            <label for="category_1">category2</label>
        </div>
        <div class="submit_button">
            <input type="submit" value="next" class="button" />
        </div>
    </form>
</div>

<div id="begin_dump" style="display:none">
    <div class="title">Step 4. 백업파일명을 입력해주세요.</div>
    <form action="./" method="get">
        <div class="category_list">
            filename : <input type="text" name="filename" value="member.xml" />
        </div>
        <div class="submit_button">
            <input type="submit" value="next" class="button" />
        </div>
    </form>
</div>

<?php include "./tpl/footer.php"; ?>
