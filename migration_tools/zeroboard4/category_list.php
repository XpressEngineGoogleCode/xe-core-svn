<?php include "./tpl/header.php"; ?>

    <form action="./" method="get" onsubmit="return doMigration(this);">
    <input type="hidden" name="path" value="<?=$path?>" />

<form action="./" method="get" onsubmit="return doMigration(this);">
<input type="hidden" name="step" value="1" />

<div id="path">
    <div class="title">Step 1. 설치된 경로를 입력해 주세요.</div>
        <ul>
            <li>
                <div class="header">path</div>
                <div class="tail"><input type="text" name="path" value="./" /></div>
                <div class="tail"><input type="button" value="next" class="button" /></div>
            </li>
        </ul>
</div>

<div id="module_list" style="display:none">
    <div class="title">Step 2. 회원정보 또는 게시판을 선택해주세요.</div>
    <form action="./" method="get" onsubmit="return doMigrationStep2(this);">
        <ul>
            <li>
                <div class="header"><label for="member">member</label></div>
                <div class="tail"><input type="radio" name="target_module" value="member" id="member" /></div>
            </li>
            <li>
                <div class="header"><input type="radio" name="target_module" value="module_board" id="module_board" /></div>
                <div class="tail"><label for="module_board">board</label></div>
            </li>
        </div>
        <div class="submit_button">
            <input type="button" value="next" class="button" />
        </div>
    </form>
</div>

<div id="category_list" style="display:none">
    <div class="title">Step 3. 카테고리를 선택해주세요.</div>
    <form action="./" method="get" onsubmit="return doMigrationStep3(this);">
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
    <form action="./" method="get" onsubmit="return doMigrationStep4(this);">
        <div class="category_list">
            filename : <input type="text" name="filename" value="member.xml" />
        </div>
        <div class="submit_button">
            <input type="submit" value="next" class="button" />
        </div>
    </form>
</div>

<?php include "./tpl/footer.php"; ?>
