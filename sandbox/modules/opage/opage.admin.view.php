<!--#include('header.html')-->

<form action="{Context::getRequestUri()}" method="get" class="issueSearch">
<input type="hidden" name="mid" value="{$mid}" />
<input type="hidden" name="act" value="{$act}" />
<input type="hidden" name="d" value="1" />
    <ul>
        <li>
            <select name="milestone_srl">
                <option value="">{$lang->milestone}</option> 
                <!--@foreach($project->milestones as $key => $val)-->
                <option value="{$val->milestone_srl}" <!--@if($val->milestone_srl==$milestone_srl)-->selected="selected"<!--@end-->>{$val->title}</option> 
                <!--@end-->
            </select>
        </li>
        <li>
            <select name="priority_srl">
                <option value="">{$lang->priority}</option> 
                <!--@foreach($project->priorities as $key => $val)-->
                <option value="{$val->priority_srl}" <!--@if($val->priority_srl==$priority_srl)-->selected="selected"<!--@end-->>{$val->title}</option> 
                <!--@end-->
            </select>
        </li>
        <li>
            <select name="type_srl">
                <option value="">{$lang->type}</option> 
                <!--@foreach($project->types as $key => $val)-->
                <option value="{$val->type_srl}" <!--@if($val->type_srl==$type_srl)-->selected="selected"<!--@end-->>{$val->title}</option> 
                <!--@end-->
            </select>
        </li>
        <li>
            <select name="component_srl">
                <option value="">{$lang->component}</option> 
                <!--@foreach($project->components as $key => $val)-->
                <option value="{$val->component_srl}" <!--@if($val->component_srl==$component_srl)-->selected="selected"<!--@end-->>{$val->title}</option> 
                <!--@end-->
            </select>
        </li>
        <li>
            <select name="package_srl" onchange="showRelease(this, this.form);">
                <option value="">{$lang->package}</option> 
                <!--@foreach($project->packages as $key => $val)-->
                <option value="{$val->package_srl}" <!--@if($val->package_srl==$package_srl)-->selected="selected"<!--@end-->>{$val->title}</option> 
                <!--@end-->
            </select>
        </li>
        <li>
            <select name="release_srl">
                <option value="">{$lang->release}</option> 
                <!--@foreach($project->packages as $key => $val)-->
                <!--@foreach($project->releases as $k => $v)-->
                <!--@if($val->package_srl == $v->package_srl)-->
                <option value="{$v->release_srl}" <!--@if($v->release_srl==$release_srl)-->selected="selected"<!--@end-->>{$v->title}</option> 
                <!--@end-->
                <!--@end-->
                <!--@end-->
            </select>
        </li>
        <li>
            <select name="assignee_srl">
                <option value="">{$lang->assignee}</option> 
                <!--@foreach($commiters as $val)-->
                <option value="{$val->member_srl}" <!--@if($val->member_srl==$assignee_srl)-->selected="selected"<!--@end-->>{$val->nick_name} ({$val->user_id})</option> 
                <!--@end-->
            </select>
        </li>
        <li>
            <!--@foreach($lang->status_list as $key => $val)-->
            <input name="status[]" type="checkbox" value="{$key}" <!--@if(in_array($key,$status))-->checked="checked"<!--@end--> id="status_{$key}"/><label for="status_{$key}" class="issue_{$key}">{$val}</label>
            <!--@end-->
        </li>
    </ul>
    <ul>
        <li class="keywordSearch">
            <select name="search_target" class="searchTarget">
                <!--@foreach($search_option as $key => $val)-->
                <option value="{$key}" <!--@if($search_target==$key)-->selected="selected"<!--@end-->>{$val}</option>
                <!--@end-->
            </select>
        </li>
        <li><input type="input" name="search_keyword" value="{htmlspecialchars($search_keyword)}" class="inputTypeText" /></li>
        <li><input type="submit" value="{$lang->cmd_search}" class="inputTypeSubmit" /></li>
        <li><input type="button" value="{$lang->cmd_cancel}" class="inputTypeSubmit" onclick="location.href='{getUrl('','mid',$mid,'act',$act)}';return false;"/></li>
        <li class="displayOpt">
            <ol>
                <!--@foreach($display_option as $key => $val)-->
                <li><input type="checkbox" name="d_{$key}" value="1" id="display_{$key}" <!--@if($val->checked)-->checked="checked"<!--@end--> <!--@if($key=='title')-->disabled="disabled"<!--@end--> /><label for="display_{$key}">{$val->title}</label></li>
                <!--@end-->
            </ol>
        </li>
    </ul>
    <div class="clear"></div>
</form>

<form action="./" method="get" class="close">
<!--@foreach($project->packages as $key => $val)-->
<select id="release_{$val->package_srl}">
    <option value="">{$lang->release}</option> 
    <!--@foreach($project->releases as $k => $v)-->
    <!--@if($val->package_srl == $v->package_srl)-->
    <option value="{$v->release_srl}" <!--@if($v->release_srl==$release_srl)-->selected="selected"<!--@end-->>{$v->title}</option> 
    <!--@end-->
    <!--@end-->
</select>
<!--@end-->
</form>

<table class="issues" cellspacing="0">
<thead>
<tr>
<!--@foreach($display_option as $k => $v)-->
    <!--@if($v->checked)-->
    <th class="{$k}">
        <div>
            <!--@if($k=='title' && $grant->is_admin)--><input type="checkbox" onclick="XE.checkboxToggleAll({ doClick:true }); return false;" /><!--@end-->
            {$v->title}
        </div>
    </th>
    <!--@end-->
<!--@end-->
</tr>
</thead>
<tbody>
<!--@foreach($issue_list as $no=>$val)-->
<tr>
    <!--@foreach($display_option as $k => $v)-->
        <!--@if($v->checked)-->
            <!--@if($k == 'no')-->
    <td class="no">{$no}</td>
            <!--@elseif($k == 'title')-->
    <td class="title issue_{$val->get('status')}">
        <!--@if($grant->is_admin)--><input type="checkbox" name="cart" value="{$val->document_srl}" onclick="doAddDocumentCart(this);" <!--@if($val->isCarted())-->checked="checked"<!--@end-->/><!--@end-->
        <a href="{getUrl('document_srl', $val->get('document_srl'))}">{$val->getTitle()}</a> 
        {$val->printExtraImages(60*60*24)}
        <!--@if($val->getCommentCount())-->
            <strong class="comment">{$val->getCommentCount()}</strong>
        <!--@end-->

        <!--@if($val->getTrackbackCount())-->
            <strong class="trackback">{$val->getTrackbackCount()}</strong>
        <!--@end-->
    </td>
            <!--@elseif($k == 'milestone')-->
    <td class="milestone"><a href="{getUrl('milestone_srl', $val->get('milestone_srl'))}">{$val->getMilestoneTitle()}</a></td>
            <!--@elseif($k == 'priority')-->
    <td class="priority"><a href="{getUrl('priority_srl',$val->get('priority_srl'))}">{$val->getPriorityTitle()}</a></td>
            <!--@elseif($k == 'type')-->
    <td class="type"><a href="{getUrl('type_srl', $val->get('type_srl'))}">{$val->getTypeTitle()}</a></td>
            <!--@elseif($k == 'component')-->
    <td class="component"><a href="{getUrl('component_srl',$val->get('component_srl'))}">{$val->getComponentTitle()}</a></td>
            <!--@elseif($k == 'status')-->
    <td class="status issue_{$val->get('status')}"><a href="{getUrl('status', $val->get('status'))}">{$val->getStatus()}</a></td>
            <!--@elseif($k == 'occured_version')-->
    <td class="occured_version"><a href="{getUrl('release_srl',$val->get('occured_version_srl'))}">{$val->getOccuredVersionTitle()}</a></td>
            <!--@elseif($k == 'package')-->
    <td class="package"><a href="{getUrl('package_srl',$val->get('package_srl'))}">{$val->getPackageTitle()}</a></td>
            <!--@elseif($k == 'regdate')-->
    <td class="regdate">{$val->getRegdate("Y-m-d")}</td>
            <!--@elseif($k == 'assignee')-->
    <td class="nick_name">
        <!--@if($val->get('assignee_srl'))-->
            <span class="member_{$val->get('assignee_srl')}">{$val->get('assignee_name')}</span>
        <!--@else-->
            &nbsp;
        <!--@end-->
    </td>
            <!--@elseif($k == 'writer')-->
    <td class="nick_name"><span class="member_{$val->getMemberSrl()}">{$val->getNickName()}</span></td>
            <!--@end-->
        <!--@end-->
    <!--@end-->
</tr>
<!--@end-->
</tbody>
</table>

<!--@if($grant->is_admin)-->
<div class="fr gap1">
    <a href="{getUrl('','module','document','act','dispDocumentManageDocument')}" onclick="popopen(this.href,'manageDocument'); return false;" class="button"><span>{$lang->cmd_manage_document}</span></a>
    <a href="{getUrl('act','dispIssuetrackerAdminManageDocument')}" onclick="popopen(this.href,'manageDocument'); return false;" class="button"><span>{$lang->cmd_manage_issue}</span></a>
</div>
<!--@end-->

<!--@if($page_navigation->total_page>1)-->
<div class="pagination a1">
    <!--@while($page_no = $page_navigation->getNextPage())-->
        <!--@if($page == $page_no)-->
            <strong>{$page_no}</strong> 
        <!--@else-->
            <span><a href="{getUrl('page',$page_no,'document_srl','')}">{$page_no}</a></span>
        <!--@end-->
    <!--@end-->
</div>
<!--@end-->
