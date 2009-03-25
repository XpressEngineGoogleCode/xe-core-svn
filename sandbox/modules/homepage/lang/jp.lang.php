<?php
    /**
     * @file   jp.lang.php
          * @author zero (zero@nzeo.com)　翻訳：ミニミ
     * @brief  CafeXE(homepage)モジュールの日本語言語パッケージ（基本的な内容のみ）
     **/

    $lang->cafe = "CafeXE"; 
    $lang->cafe_title = "ホームページ名";
    $lang->module_type = "タイプ";
    $lang->board = "掲示板";
    $lang->page = "ページ";
    $lang->module_id = "モジュール ID";
    $lang->item_group_grant = "メニューを見せるグループ";
    $lang->cafe_info = "Cafe Infomation";
    $lang->cafe_admin = "ホームページ管理者";
    $lang->do_selected_member = "選択した会員を : ";

    $lang->default_menus = array(
        'home' => 'ホーム',
        'notice' => 'お知らせ',
        'levelup' => 'レベルアップ',
        'freeboard' => '自由掲示板',
        'view_total' => '全文を表示',
        'view_comment' => '一行の物語',
        'cafe_album' => 'フォトギャラリー',
        'menu' => 'メニュー',
        'default_group1' => 'スタンバイ会員',
        'default_group2' => '準会員',
        'default_group3' => '正会員',
    );

    $lang->cmd_admin_menus = array(
        "dispHomepageManage" => "ホームページ設定",
        "dispHomepageMemberGroupManage" => "会員のグループ管理",
        "dispHomepageMemberManage" => "会員リスト",
        "dispHomepageTopMenu" => "基本メニュー 管理",
        "dispHomepageComponent" => "기능 설정",
        "dispHomepageCounter" => "접속 통계",
        "dispHomepageMidSetup" => "モジュール詳細設定",
    );
    $lang->cmd_cafe_registration = "ホームページ作成";
    $lang->cmd_cafe_setup = "ホームページ設定";
    $lang->cmd_cafe_delete = "ホームページ削除";
    $lang->cmd_go_home = "ホームへ移動";
    $lang->cmd_go_cafe_admin = 'ホームページ全体管理';
    $lang->cmd_change_layout = "変更";
    $lang->cmd_select_index = "初期ページ選択";
    $lang->cmd_add_new_menu = "新しいメニュー追加";
    $lang->default_language = "基本言語";
    $lang->about_default_language = "初めてアクセスするユーザーに見せるページの言語を指定します。";

    $lang->about_cafe_act = array(
        "dispHomepageManage" => "ホームページのレイアウトを変更します。",
        "dispHomepageMemberGroupManage" => "ホームページ内のグループを管理します。",
        "dispHomepageMemberManage" => "ホームページに登録されている会員を管理します。",
        "dispHomepageTopMenu" => "ホームページのヘッダー（header、上段）や左側などのメニューを管理します。",
        "dispHomepageComponent" => "에디터 컴포넌트/ 애드온을 활성화 하거나 설정을 변경할 수 있습니다",
        "dispHomepageCounter" => "Cafe의 접속 현황을 볼 수 있습니다",
        "dispHomepageMidSetup" => "ホームページの掲示板、ページなどのモジュールを管理します。",
    );
    $lang->about_cafe = "ホームページサービス管理者は複数のホームページ作成、および各ホームページを簡単に管理が出来ます。";
    $lang->about_cafe_title = "ホームページ名は管理をするためだけに使われ、実サービスには表示されません。";
    $lang->about_domain = "複数のホームページを作成するためには、「オリジナルドメイン」や「サブ ドメイン」のような専用のドメインが必要です。<br />また、 XEインストールパスも一緒に記入して下さい。<br />ex) www.zeroboard.com/xe";
    $lang->about_menu_names = "ホームページに使うメニュー名を言語別に指定出来ます。<br/>一個だけ記入した場合、他言語に一括適用されます。";
    $lang->about_menu_option = "メニューを選択するとき新しいウィンドウズに開けるかを選択します。<br />拡張メニューはレイアウトによって動作します。";
    $lang->about_group_grant = "選択グループのみ、メニューが見えます。<br/>全てを解除すると非会員にも見えます。";
    $lang->about_module_type = "掲示板、ページはモジュールを生成し、URLはリンクの情報のみ要ります。<br/>一度作成した後、変更は出来ません。";
    $lang->about_browser_title = "メニューにアクセスした時、ブラウザーのタイトルです。";
    $lang->about_module_id = "掲示板、ページなどにリンクさせるアドレスです。<br/>例) http://ドメイン/[モジュールID], http://ドメイン/?mid=[モジュールID]";
    $lang->about_menu_item_url = "タイプをURLにした場合、リンク先を入れて下さい。<br/>http://は省いて入力して下さい。";
    $lang->about_menu_image_button = "テキストのメニュー名の代わりに、イメージのメニューを使えます。";
    $lang->about_cafe_delete = "ホームページを削除すると、リンクされている全てのモジュール(掲示板、ページなど)とそれに付随する書き込みが削除されます。<br />ご注意下さい。";
    $lang->about_cafe_admin = "ホームページ管理者の設定が出来ます。<br/>ホームページ管理者は「http://ドメイン/?act=dispHomepageManage」にて管理者ページにアクセスが出来ます。<br />存在しない会員は管理者として登録出来ません。";

    $lang->confirm_change_layout = "レイアウトの変更時、一部のレイアウト情報が失われる可能性があります。 変更しますか?";
    $lang->confirm_delete_menu_item = "メニューの削除時、リンクされている掲示板やページモジュールも一緒に削除されます。削除しますか?";
    $lang->msg_already_registed_domain = "既に登録されているドメインです。異なるドメインを利用して下さい。";
?>
