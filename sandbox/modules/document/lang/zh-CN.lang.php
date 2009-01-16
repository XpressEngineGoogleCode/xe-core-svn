<?php
    /**
     * @file   modules/document/lang/zh-CN.lang.php
     * @author zero <zero@nzeo.com>
     * @brief  文章(document)模块语言包
     **/

    $lang->document_list = '主题目录';
    $lang->thumbnail_type = '缩略图生成方式';
    $lang->thumbnail_crop = '裁减';
    $lang->thumbnail_ratio = '比例';
    $lang->cmd_delete_all_thumbnail = '删除全部缩略图';
    $lang->move_target_module = "移动目标模块";
    $lang->title_bold = '粗标题';
    $lang->title_color = '标题颜色';

    $lang->parent_category_title = '上级分类名';
    $lang->category_title = '分类名';
    $lang->category_color = '分类颜色';
    $lang->expand = '展开';
    $lang->category_group_srls = '用户组';
    $lang->cmd_make_child = '添加下级分类';
    $lang->cmd_enable_move_category = "分类顺序(勾选后用鼠标拖动分类项)";
    $lang->about_category_title = '请输入分类名。';
    $lang->about_expand = '选择此项将维持展开状态。';
    $lang->about_category_group_srls = '被选的用户组才可以查看此分类。';
    $lang->about_category_color = '请指定分类颜色（必须带#符号）。ex）#ff0000';

    $lang->cmd_search_next = '继续搜索';

    $lang->cmd_temp_save = '临时保存';

    $lang->cmd_toggle_checked_document = '反选';
    $lang->cmd_delete_checked_document = '删除所选';
    $lang->cmd_document_do = '将把此主题..';

    $lang->msg_cart_is_null = '请选择要删除的文章。';
    $lang->msg_category_not_moved = '不能移动！';
    $lang->msg_is_secret = '这是密帖！';
    $lang->msg_checked_document_is_deleted = '删除了%d个文章。';

    // 管理页面查找的对象
    $lang->search_target_list = array(
        'title' => '标题',
        'content' => '内容',
        'user_id' => 'I D',
        'member_srl' => '会员编号',
        'user_name' => '姓名',
        'nick_name' => '昵称',
        'email_address' => '电子邮件',
        'homepage' => '主页',
        'is_notice' => '公告',
        'is_secret' => '密帖',
        'tags' => '标签',
        'readed_count' => '查看数（以上）',
        'voted_count' => '推荐数（以上）',
        'comment_count ' => '评论数（以上）',
        'trackback_count ' => '引用数（以上）',
        'uploaded_count ' => '上传附件数（以上）',
        'regdate' => '登录日期',
        'last_update' => '最近更新日期',
        'ipaddress' => 'IP 地址',
    );
?>
