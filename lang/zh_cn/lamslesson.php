<?php

/**
 * Simplified Chinese strings for lamslesson
 *
 * @package   mod_lamslesson
 * @copyright 2011 LAMS Foundation - Ernie Ghiglione (ernieg@lamsfoundation.org)
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU GPL v2
 */

defined('MOODLE_INTERNAL') || die();

$string["modulename"] = "LAMS 课程";
$string["modulenameplural"] = "LAMS 课程";
$string["modulename_help"] = "LAMS 课程模块允许教师在 Moodle 中创建 LAMS 课程。

LAMS 为教师提供直观的可视化编写环境，用于创建学习活动序列。这些活动可以包含一系列个人任务、小组协作和全班活动，既可基于内容也可基于协作。

创建序列后，可以在一个或多个课程中重复使用。

此外，LAMS 提供实时的跟进与追踪监控界面，教师可以在学生完成学习活动的过程中与其互动。

更多信息请访问：lamsfoundation.org。

";
$string["modulename_link"] = "lamslesson";
$string["lamslessonfieldset"] = "自定义示例字段集";
$string["lamslessonname"] = "课程名称";
$string["lamslessonname_help"] = "这是与 lamslessonname 字段关联的帮助提示内容。支持 Markdown 语法。";
$string["lamslesson"] = "LAMS 课程";
$string["pluginadministration"] = "LAMS 课程管理";
$string["pluginname"] = "LAMS 课程";
$string["selectsequence"] = "选择序列";
$string["sequencename"] = "序列名称";
$string["sequenceid"] = "序列 ID";
$string["displaydesign"] = "显示学习设计图像？";
$string["displaydesign_help"] = "启用后，当向学生显示课程时，将显示学习设计图。";
$string["allowlearnerrestart"] = "学生可以重新开始课程？";
$string["allowlearnerrestart_help"] = "启用后，学生可以随时重新开始课程并从头开始。每次重新开始都会清除之前的进度。";
$string["availablesequences"] = "序列";
$string["openauthor"] = "编写新的 LAMS 课程";
$string["refresh"] = "刷新";
$string["lamslesson:manage"] = "管理课程";
$string["lamslesson:participate"] = "参与课程";
$string["adminheader"] = "LAMS 服务器配置";
$string["admindescription"] = "配置你的 LAMS 服务器设置。请<strong>务必</strong>确保你在此处输入的值与 LAMS 服务器中已配置的值一致，否则集成可能无法工作。";
$string["serverurl"] = "LAMS 服务器 URL：";
$string["serverurlinfo"] = "在此输入你的 LAMS 服务器 URL，例如：http://localhost:8080/lams/。";
$string["serverid"] = "服务器 ID：";
$string["serveridinfo"] = "你在 LAMS 服务器中设置的服务器 ID 是什么？";
$string["serverkey"] = "服务器密钥：";
$string["serverkeyinfo"] = "你在 LAMS 服务器中设置的服务器密钥是什么？";
$string["validationbutton"] = "验证设置";
$string["validationheader"] = "设置验证";
$string["validationinfo"] = "在保存设置之前，请点击按钮与 LAMS 服务器进行验证。验证通过后再保存这些设置；如未通过，请检查你输入的设置是否与 LAMS 服务器中的值一致。";
$string["validationhelp"] = "需要帮助？请查看";
$string["offsetbutton"] = "计算偏移";
$string["offsetinfo"] = "如果你启用了登录请求的生存时间（TTL）限制，设置 LAMS 与 Moodle 服务器之间的时间差（分钟）非常重要。点击“计算偏移”可查看两者之间是否存在时间差。将显示的偏移时间添加到“偏移时间差”设置中。";
$string["servertimeoffset"] = "偏移时间差（分钟）";
$string["servertimeoffsetinfo"] = "这是 LAMS 与 Moodle 服务器之间的时间差（或时间偏移）。";
$string["offsetheader"] = "时间偏移";
$string["lamsmoodlehelp"] = "LAMS-Moodle 集成教程";
$string["validationsuccessful"] = "验证成功！你现在可以保存设置并开始在 Moodle 中使用 LAMS。";
$string["validationfailed"] = "验证失败：请检查你输入的设置是否与 LAMS 中的设置一致";
$string["restcallfail"] = "调用 LAMS 失败：未收到响应或连接被拒绝。请检查 LAMS 服务器 URL 是否正确，并确认服务器在线。";
$string["sequencenotselected"] = "你必须选择一个序列才能继续。";
$string["previewthislesson"] = "预览此课程";
$string["updatewarning"] = "警告：选择与当前不同的序列将为学生创建新的课程。这可能会让部分学生感到困惑。";
$string["currentsequence"] = "当前序列：";
$string["nolessons"] = "此实例中尚无 LAMS 课程。";
$string["lessonname"] = "课程名称";
$string["links"] = "链接";
$string["introduction"] = "简介";
$string["openmonitor"] = "监控此课程";
$string["lastmodified"] = "最后修改";
$string["openlesson"] = "打开课程";
$string["empty"] = "空";
$string["completionfinish"] = "当用户完成课程时显示为已完成";
$string["yourprogress"] = "你的课程进度";
$string["youhavecompleted"] = "你已完成：";
$string["outof"] = "（约）共";
$string["lessonincompleted"] = "课程尚未完成";
$string["lessoncompleted"] = "你已完成此课程";
$string["activities"] = "个活动";
$string["ymmv"] = "活动总数取决于你的学习路径。";
$string["yourmarkis"] = "你的最终成绩/评分为：";
$string["outofmark"] = "满分";
$string["lamslesson:addinstance"] = "添加 LAMS 课程";
