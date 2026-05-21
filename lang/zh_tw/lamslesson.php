<?php

/**
 * Traditional Chinese strings for lamslesson
 *
 * @package   mod_lamslesson
 * @copyright 2011 LAMS Foundation - Ernie Ghiglione (ernieg@lamsfoundation.org)
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU GPL v2
 */

defined('MOODLE_INTERNAL') || die();

$string["modulename"] = "LAMS 課程";
$string["modulenameplural"] = "LAMS 課程";
$string["modulename_help"] = "LAMS 課程模組允許教師在 Moodle 中建立 LAMS 課程。

LAMS 為教師提供直觀的視覺化編寫環境，用於建立學習活動序列。這些活動可包含多種個人任務、小組協作與全班活動，並可同時基於內容與協作。

序列建立後，可在一門或多門課程中重複使用。

此外，LAMS 提供即時的追蹤與監控介面，教師可在學生進行學習活動時與其互動。

更多資訊請造訪：lamsfoundation.org。

";
$string["modulename_link"] = "lamslesson";
$string["lamslessonfieldset"] = "自訂範例欄位集";
$string["lamslessonname"] = "課程名稱";
$string["lamslessonname_help"] = "這是與 lamslessonname 欄位關聯的說明提示內容。支援 Markdown 語法。";
$string["lamslesson"] = "LAMS 課程";
$string["pluginadministration"] = "LAMS 課程管理";
$string["pluginname"] = "LAMS 課程";
$string["selectsequence"] = "選擇序列";
$string["sequencename"] = "序列名稱";
$string["sequenceid"] = "序列 ID";
$string["displaydesign"] = "顯示學習設計圖像？";
$string["displaydesign_help"] = "啟用後，向學生顯示課程時會顯示學習設計圖。";
$string["allowlearnerrestart"] = "學生可以重新開始課程？";
$string["allowlearnerrestart_help"] = "啟用後，學生可隨時重新開始課程並從頭開始。每次重新開始都會清除先前的進度。";
$string["availablesequences"] = "序列";
$string["openauthor"] = "編寫新的 LAMS 課程";
$string["refresh"] = "重新整理";
$string["lamslesson:manage"] = "管理課程";
$string["lamslesson:participate"] = "參與課程";
$string["adminheader"] = "LAMS 伺服器設定";
$string["admindescription"] = "設定你的 LAMS 伺服器參數。請<strong>務必</strong>確保你在此輸入的值與 LAMS 伺服器中已設定的值一致，否則整合可能無法運作。";
$string["serverurl"] = "LAMS 伺服器 URL：";
$string["serverurlinfo"] = "在此輸入你的 LAMS 伺服器 URL，例如：http://localhost:8080/lams/。";
$string["serverid"] = "伺服器 ID：";
$string["serveridinfo"] = "你在 LAMS 伺服器中設定的伺服器 ID 是什麼？";
$string["serverkey"] = "伺服器金鑰：";
$string["serverkeyinfo"] = "你在 LAMS 伺服器中設定的伺服器金鑰是什麼？";
$string["validationbutton"] = "驗證設定";
$string["validationheader"] = "設定驗證";
$string["validationinfo"] = "在儲存設定之前，請按下按鈕與 LAMS 伺服器進行驗證。驗證正確後再儲存這些設定；若驗證失敗，請檢查你輸入的設定是否與 LAMS 伺服器中的值相符。";
$string["validationhelp"] = "需要協助？請參考";
$string["offsetbutton"] = "計算偏移";
$string["offsetinfo"] = "如果你啟用了登入請求的存活時間（TTL）限制，設定 LAMS 與 Moodle 伺服器之間的時間差（分鐘）非常重要。點擊「計算偏移」以查看兩者是否有時間差。將顯示的偏移時間加到「偏移時間差」設定中。";
$string["servertimeoffset"] = "偏移時間差（分鐘）";
$string["servertimeoffsetinfo"] = "這是 LAMS 與 Moodle 伺服器之間的時間差（或時間偏移）。";
$string["offsetheader"] = "時間偏移";
$string["lamsmoodlehelp"] = "LAMS-Moodle 整合教學";
$string["validationsuccessful"] = "驗證成功！你現在可以儲存設定並開始在 Moodle 中使用 LAMS。";
$string["validationfailed"] = "驗證失敗：請檢查你輸入的設定是否與 LAMS 中的設定相符";
$string["restcallfail"] = "呼叫 LAMS 失敗：未收到回應或連線被拒絕。請檢查 LAMS 伺服器 URL 是否正確，並確認伺服器在線。";
$string["sequencenotselected"] = "你必須選擇一個序列才能繼續。";
$string["previewthislesson"] = "預覽此課程";
$string["updatewarning"] = "警告：選擇與目前不同的序列將為學生建立新的課程，這可能會讓部分學生感到困惑。";
$string["currentsequence"] = "目前序列：";
$string["nolessons"] = "此實例中尚無 LAMS 課程。";
$string["lessonname"] = "課程名稱";
$string["links"] = "連結";
$string["introduction"] = "簡介";
$string["openmonitor"] = "監控此課程";
$string["lastmodified"] = "最後修改";
$string["openlesson"] = "開啟課程";
$string["empty"] = "空";
$string["completionfinish"] = "當使用者完成課程時顯示為已完成";
$string["yourprogress"] = "你的課程進度";
$string["youhavecompleted"] = "你已完成：";
$string["outof"] = "（約）共";
$string["lessonincompleted"] = "課程尚未完成";
$string["lessoncompleted"] = "你已完成此課程";
$string["activities"] = "個活動";
$string["ymmv"] = "活動總數取決於你的學習路徑。";
$string["yourmarkis"] = "你的最終成績/評分為：";
$string["outofmark"] = "滿分";
$string["lamslesson:addinstance"] = "新增 LAMS 課程";
