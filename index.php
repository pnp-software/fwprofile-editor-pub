<?php
   ob_start("ob_gzhandler");
   require('version.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head><title>FW Profile Editor for State Machines and Procedures</title>
<meta name="description" content="Edit State Machines and Procedures using web user interface and generate C code by just one click">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="en">
<link rel="shortcut icon" href="favicon.ico">

<link rel="stylesheet" href="css/bootstrap.min.css<?php echo $rev; ?>">
<link rel="stylesheet" href="css/sh_typical.min.css<?php echo $rev; ?>">

<style>

html, body { margin: 0; width: 100%; height: 100%; }
html, body, td, pre { -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
.editbox input, .editbox textarea, .editbox select { outline: none !important; border: 1px solid #222 !important; -webkit-border-radius: 0px !important; -moz-border-radius: 0px !important; border-radius: 0px !important; }
::-ms-clear { display: none; }
.btn { outline: none !important; }

.sh_cpp { padding: 0; margin-right: 20px; border: 0; background-color: white; margin-left: 20px; font-size: 12px; }

.rotate {
       -webkit-transform: rotate(-90deg);
       -moz-transform: rotate(-90deg);
       -ms-transform: rotate(-90deg);
       -o-transform: rotate(-90deg);
       filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
}

.colorItem { cursor: pointer; border: 1px solid white; display: block; float: left; margin: 2px; width: 25px; height: 25px;  }
.toolbar
{
   -webkit-transition: all 0.2s ease-in-out;
   -moz-transition: all 0.2s ease-in-out;
   -o-transition: all 0.2s ease-in-out;
   -ms-transition: all 0.2s ease-in-out;
   transition: all 0.2s ease-in-out;
}
.editbox
{
   -webkit-transition: opacity 0.2s ease-in-out;
   -moz-transition: opacity 0.2s ease-in-out;
   -o-transition: opacity 0.2s ease-in-out;
   -ms-transition: opacity 0.2s ease-in-out;
   transition: opacity 0.2s ease-in-out;
}
.thumbnail
{
   -webkit-transition: none;
   -moz-transition: none;
   -o-transition: none;
   -ms-transition: none;
   transition: none;
}
.alertbox
{
   border: 1px solid white;
   font-family: Arial;
   -webkit-border-radius: 3px; -moz-border-radius: 3px; border-radius: 3px;
   width: 380px;
   padding: 10px;
   cursor: pointer;
}
.alertboxcontainer
{
   position: fixed; top: -200px; left: 50%; margin-left: -200px; width: 400px;
   -webkit-transition: all 0.2s ease-in-out;
   -moz-transition: all 0.2s ease-in-out;
   -o-transition: all 0.2s ease-in-out;
   -ms-transition: all 0.2s ease-in-out;
   transition: all 0.2s ease-in-out;
}

.toolbar { background-color: #222; overflow: hidden; position: fixed; top: 0px; left: 0px; width: 40px; height: 100%; padding: 5px; }
.toolbar button { border: 1px solid #222; cursor: pointer; width: 30px; height: 30px; font-weight: bold; background-color: white; font-family: Arial; -webkit-border-radius: 3px; -moz-border-radius: 3px; border-radius: 3px; }
.toolbar tr.menurow { cursor: pointer; }
.menurow i.pull-right { margin-top: 2px !important;}
.toolbardescr { color: white; font-family: Arial; font-size: 15px; padding-left: 10px; padding-right: 10px;}
.toolbarsepa { height: 30px; }
.hidden { display: none; }

.radius5 {  -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px;  }

.editbox { position: fixed; top: 10px; right: 10px; }
.editbox { font-family: Arial; font-size: 12px; background-color: #444; color: #fff; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; box-shadow: 5px 5px 5px #ccc;  }
.editboxlabel { text-align: center; padding: 5px; cursor: move; }
.editboxcontent { padding: 10px; border-top: 1px solid #666;  }

.suggest {
   max-height: 200px;
   overflow: hidden;
   overflow-y: auto;
   position: absolute;
   left: 0; top: 0;
   background-color: #fff;
   border: 1px solid #222;
   z-index: 100;
}
.suggest a { display: block; padding-left: 6px; padding-right: 6px; color: black; text-decoration: none; cursor: default; font-size: 12px; white-space: nowrap; }
.suggest a:hover { background-color: #6599FF; color: #fff }
.suggest a:first-child { background-color: #6599FF; color: #fff }

.suggest.rounded { border: 1px solid #999; }
.suggest.rounded a { padding-top: 3px; padding-bottom: 3px; }
.suggest.sharp { border: 1px solid #000; }

.checkall { cursor: pointer; }

.menu
{
   margin-left: 5px;
   margin-right: 5px;
   padding: 16px;
   padding-left: 17px; padding-right: 17px;
   border-left: 1px solid white;
   border-right: 1px solid white;
   cursor: pointer;
   -webkit-border-top-left-radius: 10px;
   -webkit-border-top-right-radius: 10px;
   -moz-border-radius-topleft: 10px;
   -moz-border-radius-topright: 10px;
   border-top-left-radius: 10px;
   border-top-right-radius: 10px;
}

.menu.active, .menu.active:hover
{
   background-color: white;
   padding: 17px;
   border: 1px solid #aaa;
   border-bottom: 0;
   color: black;
   box-shadow: -2px -2px 0px #eeeeee;
}

.menu:hover
{
   background-color: #eee;
   border: 1px solid #aaa;
   color: black;
}

.labelAP { display: inline; font-size: 11px; position: relative; top: 2px;}

.thumbnail { width: 400px; }
.thumbnail img {  border: 1px solid #fff; -webkit-border-radius: 3px; -moz-border-radius: 3px; border-radius: 3px; width: 400px; }
.previewcontainer {  margin-right: 450px; }
.itemname { border: 1px solid white; padding: 3px; padding-left: 10px; margin-left: -11px; cursor: pointer;  margin-right: 500px; }
.saveasdlg { margin-right: 500px; }
.itemname:hover, .itemnamehover { background-color: #e7f3ff; border-color: #b9d5f1;}
.hrline { height: 1px; margin-left: 0px; border-bottom: 1px dashed #eee; margin-bottom: 20px; margin-right: 500px; }
.modalperc { width: 80% }
.modlabel { font-size: 24.5px; font-weight: bold; }
.modalmenu { margin-left: 40px; }
.loginout { margin-top: 3px; }
.fileab { display: none; }
.itemnamehover .fileab { display: block; }
.fileab i:hover { opacity: 1 !important; }
.fileab b:hover { color: #ff0000 !important; }

.filetags { float: right; margin-right: 50px; display: none; }
.itemnamehover .filetags { margin-right: 16px; }
.chboximg { opacity: 0.2; position: relative; top: -2px; margin: -7px; margin-right: 6px; margin-left: -13px; border: 10px solid transparent; border-right: 4px solid transparent; }
.chboximg:hover { opacity: 1; }
.chboximg.icon-check { opacity: 1 !important; }
.savelist .chboximg { display: none; }
.tagfilter { margin-right: 520px; }
.projsel { margin-right: 500px; }

@media (max-width: 1300px) {
   .thumbnail, .thumbnail img { width: 300px;  }
   .previewcontainer {  margin-right: 350px; }
   .itemname, .itemnamehover { margin-right: 400px; }
   .saveasdlg { margin-right: 400px; }
   .hrline { margin-right: 400px; }
   .modalperc { width: 90% }
   .tagfilter { margin-right: 450px; }
   .projsel { margin-right: 400px; }
}

@media (max-width: 1100px) {
   .menu { padding-left: 10px; padding-right: 10px; margin-left: 0; margin-right: 0; }
   .modlabel { font-size: 20px; }
   .modalmenu { margin-left: 20px; }
   .loginout { margin-top: 2px; }
}

.qmark, .qmarkR
{
   cursor: pointer;
   margin-left: 5px;
   position: relative; top: -1px;
}
.qmarkR { float: right; margin-right: -10px; }

div,p { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; }
.print { display: none; }

.projectbox blockquote:hover { cursor: pointer; border-left: 5px solid #777; }

.projectbox.collapsed + div .hrline,
.projectbox.collapsed + div br,
.projectbox.collapsed .itemname,
.projectbox.collapsed .projsel
{ display: none !important; }

.projectbox blockquote { margin-bottom: 10px; }
.projectbox.collapsed blockquote { margin-bottom: 5px; }

.revisionhoverlink
{
   text-decoration: underline;
}

</style>

<style type="text/css" media="print">
   .noprint { display: none; }
   .print { display: block; max-width: 100%; }
</style>

</head>

<body leftMargin="0" topMargin="0" marginheight="0" marginwidth="0" style='overflow: hidden'>
<script type="text/javascript" src="js/jquery.min.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="js/jquery.ui.min.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="js/jquery.slimscroll.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="js/raphael-min.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="js/sh_main.min.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="js/sh_cpp.min.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="js/bootstrap-filestyle.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="js/diacritic2ascii.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="js/jszip.min.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="js/filesaver.js<?php echo $rev; ?>"></script>

<script type="text/javascript" src="lib.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="dragfuncs.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="selectfuncs.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="shortcuts.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="toolbars.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="props.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="history.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="loadsave.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="tabs.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="actions.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="codegenlib.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="codegenPR.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="codegenSM.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="offline.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="payment.js<?php echo $rev; ?>"></script>

<!-- noprint section start -->
<div class=noprint>

<!-- cover entire screen -->
<div id=sizer style="position: absolute; top: 0; left:0; width: 100%; height: 100%; background-color: #eee;"></div>

<!-- svg graphics goes to the working desk (which holds one drawing paper) -->
<div id=desk></div>

<!-- edit box (properties for elements) -->
<table cellspacing=0 cellpadding=0 id=editbox class=editbox>
   <tr><td style='width: 20px'></td><td id=editboxlabel class=editboxlabel>Information box</td><td style='width: 20px'><i class="icon-minus-sign icon-white pull-right" style="cursor: pointer; margin: 5px;" id=editboxminimizer></i></td></tr>
   <tr><td id=editboxcontent class=editboxcontent colspan=3 style='overflow: hidden'>

   <!-- scrollable area inside the edit box -->
   <div id=editboxscrollablearea style='overflow-y: auto; overflow-x: hidden; white-space: nowrap;'>

   <!-- global properties form -->
   <div id=globalprop class=hidden>
      <span id=nameLabel>State Machine Name:</span><br>
      <input type=text style='width: 310px;' data-filter="freeform" placeholder="state machine name" id=epropSmName><br>
      <span id=tagLabel>Project name:</span><br>
      <input type=text style='width: 310px;' data-filter="noregex" placeholder="Project name (optional)" id=epropSmTags data-suggest="tags"><br>
      User-Defined global variables:
      <div id=globvar>
         <div>
           <div class='form-inline globrow' style='margin-bottom: 5px;'>
             <select class='input-mini globvartype' style='width: 85px;'><option>int<option>short<option>long<option>char<option>uint<option>ushort<option>ulong<option>uchar<option>double<option>float</select><input type=text class='input-small globvarname' placeholder='variable name' data-filter="varname" style='width: 107px;' data-enter="global_variable_add"><input type=text placeholder='=value' class='input-mini globvarvalue' data-filter="number" data-enter="global_variable_add">
             <button class='btn btn-success' style='height: 30px;' id=globvaraddbtn><i class='icon-plus icon-white' style='margin-bottom: 2px;'></i></button>
           </div>
         </div>
      </div>
      <div id=globadd></div>
      Custom includes:
      <br><textarea id=epropSmIncludes rows=2 class='input-xlarge' style='width: 310px; font-size: 11px; line-height: 16px; font-family: monospace;' placeholder='#include "yourfile.h"'></textarea><br>
      Notes:
      <br><textarea id=epropSmNotes rows=2 class='input-xlarge' style='width: 310px;' placeholder='notes'></textarea>


      <br><span id=orderShowLabel>Show transition order:</span><span class=qmark id=orderqmarkbtn><img src=img/qmark.png></span><br>
      <div class="btn-group" data-toggle="buttons-radio" style='margin-bottom: 10px;' id="displayOrder">
         <button type="button" class="btn btn-info" id=displayOrder0btn ><i class="icon-ok icon-white" id=displayOrder0></i> no</button>
         <button type="button" class="btn" id=displayOrder1btn><i class="icon-ok icon-white hidden" id=displayOrder1></i> yes</button>
      </div>

      <br>Show text on diagram:<br>
      <div class="btn-group" data-toggle="buttons-radio" style='margin-bottom: 5px;' id="displayInfo">
         <button type="button" class="btn btn-info" id=displayInfo0btn><i class="icon-ok icon-white" id=displayInfo0></i> auto</button>
         <button type="button" class="btn" id=displayInfo1btn><i class="icon-ok icon-white hidden" id=displayInfo1></i> functions/code</button>
         <button type="button" class="btn" id=displayInfo2btn><i class="icon-ok icon-white hidden" id=displayInfo2></i> descriptions</button>
      </div>

   </div>

   <!-- state properties form -->
   <div id=stateprop class=hidden>
     <div>
        <span id=stateidLabel>State Identifier:</span><br>        
        <input type=text class="input-xlarge" placeholder=identifier data-filter="varname" id=epropStateID></br>
     </div>

     <div id=epropEntry>
       <span id=entryActionLabel>Entry Action:</span><span class=qmarkR id=entryacqmark><img src=img/qmark.png></span><br>
       <div><input type=checkbox id=epropEntryAp><label class=labelAP for=epropEntryAp> Adaptation Point</label></br></div>
       <select class="input-small" style='width: 120px;' id=epropEntryType><option>call function<option>define function<option>run code</select><input type=text class="input-small" style='width: 138px;' id=epropEntryFunc placeholder="function name" data-filter="funcname" data-suggest="action"><br>
       <div class=hidden><textarea rows=2 class='input-xlarge' id=epropEntryCode placeholder="# code to be executed, e.g.: var++;"></textarea></div>
       <div><textarea rows=2 class='input-xlarge' id=epropEntryDesc placeholder="action description"></textarea></div>
     </div>

     <div id=epropDo class=hidden>
        Do Action:<span class=qmarkR id=doacqmark><img src=img/qmark.png></span><br>
        <div><input type=checkbox id=epropDoAp><label class=labelAP for=epropDoAp> Adaptation Point</label></br></div>
        <select class="input-small" style='width: 120px;' id=epropDoType><option>call function<option>define function<option>run code</select><input type=text class="input-small" style='width: 138px;' id=epropDoFunc placeholder="function name" data-filter="funcname" data-suggest="action"><br>
        <div class=hidden><textarea rows=2 class='input-xlarge' id=epropDoCode placeholder="# code to be executed, e.g.: var++;"></textarea></div>
        <div><textarea rows=2 class='input-xlarge' id=epropDoDesc placeholder="action description"></textarea></div>
     </div>

     <div id=epropExit class=hidden>
        Exit Action:<span class=qmarkR id=exitacqmark><img src=img/qmark.png></span><br>
        <div><input type=checkbox id=epropExitAp><label class=labelAP for=epropExitAp> Adaptation Point</label></br></div>
        <select class="input-small" style='width: 120px;' id=epropExitType><option>call function<option>define function<option>run code</select><input type=text class="input-small" style='width: 138px;' id=epropExitFunc placeholder="function name" data-filter="funcname" data-suggest="action"><br>
        <div class=hidden><textarea rows=2 class='input-xlarge' id=epropExitCode placeholder="# code to be executed, e.g.: var++;"></textarea></div>
        <div><textarea rows=2 class='input-xlarge' id=epropExitDesc placeholder="action description"></textarea></div>
     </div>

     <div id=epropEmbedSM>
        Embed another saved diagram:<span class=qmarkR id=embedsmqmark><img src=img/qmark.png></span><br>
        <select id=epropEmbedSmId style='width: 238px;' class='input-xlarge'></select>
        <button id=opentabbtn class="btn btn-success" style="height: 30px; width: 40px; margin-top: -9px;"><i class="icon-pencil icon-white"></i></button>
        <br>
     </div>

     Notes:<br><textarea rows=2 id=epropStateNote class='input-xlarge' placeholder="notes"></textarea><br>

     Color:
     <span class=colorpickerHTML></span>
   </div>


   <!-- choice state properties form -->
   <div id=choiceprop class=hidden>
     <span id=choiceidLabel>Choice Identifier:</span><br><input type=text class="input-xlarge" id=epropChoiceID placeholder=identifier data-filter="varname"><br>
     Notes:<br><textarea rows=2 class='input-xlarge' id=epropChoiceNote placeholder="notes"></textarea><br>
     Color:
     <span class=colorpickerHTML></span>
   </div>


   <!-- initial state properties form -->
   <div id=initprop class=hidden>
     Notes:<br><textarea id=epropInitNote rows=2 class='input-xlarge' placeholder="notes"></textarea><br>
     Color:
     <span class=colorpickerHTML></span>
   </div>


   <!-- note properties form -->
   <div id=noteprop class=hidden>
     Note:<br>
     <textarea id=epropNoteNote rows=2 class='input-xlarge' placeholder="notes"></textarea><br>
     <button class='btn btn-success' style='height: 30px;' id=noteconnectbtn><i class="icon-plus icon-white" style="margin-bottom: 2px;"></i></button>
     <span style='position: relative; top: 3px;'> &nbsp; Add note connector</span><br><br>
     Color:
     <span class=colorpickerHTML></span>
   </div>

   <!-- final state properties form -->
   <div id=finalprop class=hidden>
     Notes:<br><textarea id=epropFinalNote rows=2 class='input-xlarge' placeholder="notes"></textarea><br>
     Color:
     <span class=colorpickerHTML></span>
   </div>


   <!-- transition properties form -->
   <div id=transprop class=hidden>
     <table border=0 cellspacing=0 cellpadding=0 id=triggerguardprop>
        <tr id=triggeridprop><td>
          Transition Trigger Identifier:<br>
          <input type=text class="input-xlarge" placeholder=identifier id=epropTrigger data-filter="varname" data-suggest="trigger">
        </td></tr>
        <tr><td>Guard:<span class=qmarkR id=guardqmark style='margin-right: 5px;'><img src=img/qmark.png></span></td></tr>
        <tr id=epropTrGuard><td>
        <div>
              <div><input type=checkbox id=epropTrGuardAp><label class=labelAP for=epropTrGuardAp> Adaptation Point</label></br></div>
              <select class="input-small" style='width: 120px;' id=epropTrGuardType><option>call function<option>define function<option>run code<option disabled=true>--------------------<option>Else<option>Wait N cycles</select><input type=text id=epropTrGuardFunc class="input-small" style='width: 138px;' data-filter="funcname" data-suggest="guard" placeholder="function name">
              <div class=hidden><textarea rows=2 class='input-xlarge' id=epropTrGuardCode placeholder="# return evaluation, e.g.: return val &gt; 10;"></textarea><br></div>
              <div><textarea rows=2 class='input-xlarge' id=epropTrGuardDesc placeholder="guard description"></textarea></div>
        </div>
        <div>
           Order:<br>
           <select id=epropTrGuardOrder style="width: 280px;"><option>1</select>
        </div>
        </td></tr>
     </table>

     <div id=epropTrAction>
        Action:<span class=qmarkR id=tractionqmark style='margin-right: 5px;'><img src=img/qmark.png></span><br>
        <div><input type=checkbox id=epropTrActionAp><label class=labelAP for=epropTrActionAp> Adaptation Point</label></br></div>
        <select class="input-small" style='width: 120px;' id=epropTrActionType><option>call function<option>define function<option>run code</select><input type=text class="input-small" id=epropTrActionFunc style='width: 138px;' placeholder="function name" data-filter="funcname" data-suggest="action">
        <div class=hidden><textarea rows=2 class='input-xlarge' id=epropTrActionCode placeholder="# code to be executed, e.g.: var++;"></textarea></div>
        <div><textarea rows=2 class='input-xlarge' id=epropTrActionDesc placeholder="action description"></textarea></div>
     </div>

     Notes:<br><textarea rows=2 class='input-xlarge' id=epropTrNote placeholder="notes"></textarea>
   </div>

   </div>
</td></tr>
</table>

<!-- alert box (error messages, OK/Cancel dialogs) -->
<div class=alertboxcontainer>
<table cellspacing=0 cellpadding=0 align=center border=0><tr><td>
<div style="margin: 10px;" id=alertbox class=alertbox align=center></div>
</td></tr></table>
</div>

<!-- top line -->
<!-- <div style="position: fixed; top: 0px; left: 40px; background-image: url('img/topline.png'); height: 3px; width: 100%"></div> -->

<!-- left toolbar -->
<div id=toolbar class=toolbar>

  <table border=0 cellspacing=0 cellpadding=5 align=left width=110 height='100%' id=toolbartbl>
    <tr class=menurow id=toolbaropenbutton><td width=30><button class=toolbarbut><i class=icon-folder-open></i></button></td><td class=toolbardescr>Files</td></tr>
    <tr class=menurow id=toolbarcodebutton><td><button class=toolbarbut><i class=icon-list></i></button></td><td class=toolbardescr>Code</td></tr>

    <tr class=toolbarsepa><td></td></tr>
    <tr class="menurow nomenu" id=historybutton><td><button class=toolbarbut><i class=icon-share-alt></i></button></td><td class=toolbardescr>Undo</td></tr>
    <tr class="menurow nomenu" id=selfconnect><td><button class=toolbarbut><i class=icon-retweet></i></button></td><td class=toolbardescr>To&nbsp;Self</td></tr>
    <tr class="menurow nomenu" id=deletebutton><td><button class=toolbarbut><i class=icon-trash></i></button></td><td class=toolbardescr>Delete</td></tr>

    <tr class=toolbarsepa><td></td></tr>
    <tr class="menurow nomenu" id=addstateI><td><button class=toolbarbut><i class=icon-play-circle></i></button></td><td class=toolbardescr>Initial</td></tr>
    <tr class="menurow nomenu" id=addstateF><td><button class=toolbarbut><i class=icon-off></i></button></td><td class=toolbardescr>Final</td></tr>
    <tr class="menurow nomenu" id=addstateC><td><button class=toolbarbut><i class=icon-random></i></button></td><td class=toolbardescr id=choiceButLabel>Choice</td></tr>
    <tr class="menurow nomenu" id=addstateS><td><button class=toolbarbut><i class=icon-list-alt></i></button></td><td class=toolbardescr id=stateButLabel>State</td></tr>
    <tr class="menurow nomenu" id=addstateN><td><button class=toolbarbut><i class=icon-comment></i></button></td><td class=toolbardescr id=noteButLabel>Note</td></tr>

    <tr class=toolbarsepa><td></td></tr>
    <tr class="menurow nomenu" id=helpbutton><td><button class=toolbarbut><i class=icon-question-sign></i></button></td><td class=toolbardescr>Help</td></tr>

    <tr><td colspan=2 style="height: 100%;" valign=bottom>
       <!-- logo -->
       <table border=0 width=120 cellspacing=0 cellpadding=0>
       <tr><td width=30><a href="http://www.pnp-software.com/fwprofile/" target=_blank><img border=0 src=img/logo.png style='margin-bottom: 15px; margin-left: 3px; margin-top: 15px;'></a>
       </td><td valign=bottom><div class=rotate style='color: #fff; font-size: 12px; white-space: nowrap; margin-bottom: 45px;'>(c) P&amp;P Software<br>version <?php echo $version; ?></div>
       </td></tr></table>
    </td></tr>
  </table>

</div>




<!-- suggestion box for actions, guards and triggers in INPUT edit -->
<div id=suggest class="suggest hidden">
</div>

<div id=payment class=hidden>
<div id=paymentbg style="position: fixed; background-color: #000; left: 0; top: 0; width: 100%; height: 100%; opacity: 0.8;"></div>
<div id=paymentdialog style="position: fixed; left: 0; top: 0; width: 100%; height: 100%;">
<div style='margin: auto; margin-top: 200px; background-color: #fff; border: 1px solid silver; border-radius: 5px; width: 400px; padding: 20px;'>
<div id=paynoaccess>
  <h1>Sign in to get access</h1>
  <br>Thank you for using FW Profile Editor.
  Please make sure you sign in using your Google account. If you are not signed in, a new window with login form should open.
  The sign in process is needed to grant you your free trial access.<br><br>
  <button class="btn btn-info" id=chromeloginretry>Sign In</button>
  <div id=payloginprogress class="progress progress-striped active" style='width: 400px; height: 30px;'>
    <div class="bar" style="width: 100%;"></div>
  </div>
</div>
<div id=paysoonmsg>
  <h1>Your free trial is ending soon.</h1>
  <br>Thank you for using FW Profile Editor.
  Your free trial is ending soon. You can continue using it for <b id=freedaysleft>3</b> more days,
  then you will be required to purchase. Thank you very much for your support.<br><br>
  <button class="btn btn-info" id=freetrialcontinue>Continue</button>
</div>
<div id=paynowmsg>
  <h1>Your free trial is over.</h1>
  <br>Thank you for using FW Profile Editor.
  Your free trial is over. Please consider purchasing to support further development. Thank you very much for your support.<br><br>
  <button class="btn btn-success" id=buynowbtn>Buy now</button>
</div>

</div>
</div>
</div>

<!-- modal dialog -->
<div id=modal class="hidden">
   <div id=modalbg style="position: fixed; background-color: #000; left: 0; top: 0; width: 100%; height: 100%;"></div>
   <table id=modaltbl class="hidden" border=0 cellspacing=0 cellpadding=0 style="position: fixed; left: 0; top: 0; width: 100%; height: 100%;">
     <tr><td>
        <div style="position: fixed; left: 0; top: 0; width: 100%; height: 100%;" id=modaldestroy></div>
        <div id=modalwindow class="radius5 modalperc" style='background-color: #fff; margin: auto; overflow: hidden; padding: 10px; cursor: default;'>
           <table border=0 cellspacing=0 cellpadding=0 style='width: 100%'>
              <tr><td id=modalheader valign=top>
                 <div class="modal-header" style='cursor: default; border-bottom: 1px solid #aaa; line-height: 30px;'>
                    <button type="button" class="close" style='margin-left: 30px; margin-top: 10px; outline: none;' id=modaldestroybtn>&times;</button>
                    <div id=modallogout class="pull-right hidden loginout"><span class='menu' id=logoutmousedown>Logout</span></div>
                    <div id=modallogin class="pull-right hidden loginout"><span id='loginpromptmenu' class='menu'>Sign In</span></div>
                    <div id=modalident class="pull-right hidden loginout" style='margin-right: 30px;'></div>
                    <div class="pull-right" style='margin-right: 20px;'>
                       <img id=waitimg style='display: none;' src=img/wait32.gif>
                    </div>

                    <span style='line-height: 30px; padding-bottom: 20px;'>
                       <span class=modlabel id=modallabel>Manage Files</span>
                       <span id=modalmenu class=modalmenu>
                          <span id='presetsmenu' class='menu'>Presets</span>
                          <span id='filesmenu' class='menu'>Load</span>
                          <span id='saveasmenu' class='menu'>Save</span>
                          <span id='importmenu' class='menu'>Actions <span class="badge badge-warning actionscnt" style='position: relative; top: -8px; margin-left: 5px;'></span></span>
                       </span>
                    </span>
                 </div>
              </td></tr>
              <tr><td valign=top>

                 <div id=modalcontent style='overflow: hidden; padding-top: 0px; padding-bottom: 0px;'>
                 <br>

                    <div id=loginprompt class=hidden>
                       <div style='margin-left: 20px; margin-right: 20px; max-width: 800px; text-align: justify;'>
                          <blockquote>Sign in</blockquote>
                          <div style='margin-left: 20px;'>
                             <p>This website has more to offer when you sign in to your Account. Save and restore your diagrams,
                             download generated code as ZIP archive, and many more. Please sign in with your existing Account now, or register one, it's free.
                             </p><p>
                             <strong>IMPORTANT WARNING</strong>: Free access is offered <strong>ENTIRELY AT THE USER's OWN RISK</strong>. 
                             We offer <strong>NO GUARANTEE OF ANY KIND</strong>
                             about the stability, persistence, integrity or security of data which users choose to store on our server.
                             </p><p>
                             If you prefer a local installation of the editor, you can download a GPLv3 version
                             from <a href="https://github.com/pnp-software/fwprofile-editor-pub">here</a>.     
                             <a href="mailto:pnp-software@pnp-software.com">Contact us</a> if you want a local installation on a commercial license.
                             <div id=logorreg style='white-space: nowrap'><br>
                                 <button class='btn btn-success btn-large' id=loginbtn><i class='icon-user icon-white'></i> &nbsp;Sign in</button> &nbsp; &nbsp;
                                 -or- &nbsp; &nbsp; <button class='btn btn-large' id=registerbtn><i class='icon-pencil'></i> &nbsp;Register</button>
                             </div>

                             <div class=hidden id=loginform><br>
                                <form method=post target=hidiframe action=blank.php style='margin: 0px;'>
                                  <table border=0 cellspacing=0 cellpadding=0>
                                    <tr><td style='width: 80px;'><label class='control-label'>E-mail:</label></td><td><input name="loginEmail" class=input-xlarge type="text" id="loginEmail" placeholder="your@email"></td><td><div class="control-group error"><span id=loginfailtext class="help-inline" style='margin-left: 10px'></span></div></td></tr>
                                    <tr><td style='width: 80px;'><label class='control-label'>Password:</label></td><td><input name="loginPass" class=input-xlarge type="password" id="loginPass" placeholder="your password"></td><td><div class="control-group info"><span id=mailpasstext class="help-inline" style='margin-left: 10px;'></span></div></td></tr>
                                    <tr><td></td><td><button type="submit" class="btn btn-success" id=signinbtn><i class='icon-user icon-white'></i> &nbsp;Sign In</button>
                                    <button class='btn btn-link' id=forgetpasslink>forgot password?</button>
                                    <button class='btn btn-link' id=backlink>back</button>
                                    </td><td></td></tr>
                                  </table>
                                </form>
                                <iframe src=blank.php class=hidden id=hidiframe name=hidiframe></iframe>
                             </div>

                             <div class=hidden id=forgotpassform><br>
                                  <table border=0 cellspacing=0 cellpadding=0>
                                    <tr><td style='width: 80px;'><label class='control-label'>E-mail:</label></td><td><input class=input-xlarge type="text" id="loginEmailPassReset" placeholder="your@email"></td><td><div class="control-group error"><span id=passresettext class="help-inline" style='margin-left: 10px'></span></div></td></tr>
                                    <tr id=rescoderow class=hidden><td style='width: 110px;'><label class='control-label'>Reset code:</label></td><td><input class=input-xlarge type="text" id="rescode" placeholder="enter code you received by email"></td><td><div class="control-group error"><span id=rescodetext class="help-inline" style='margin-left: 10px'></span></div></td></tr>
                                    <tr id=respw1row class=hidden><td style='width: 110px;'><label class='control-label'>New password:</label></td><td><input class=input-xlarge type="password" id="respw1"></td><td></td></tr>
                                    <tr id=respw2row class=hidden><td style='width: 110px;'><label class='control-label'>Type again:</label></td><td><input class=input-xlarge type="password" id="respw2"></td><td><div class="control-group error"><span id=respw2text class="help-inline" style='margin-left: 10px'></span></div></td></tr>
                                    <tr><td></td><td><button class="btn btn-info" id=passresetbtn><i class='icon-repeat icon-white'></i> &nbsp;Reset Password</button>
                                    <button class='btn btn-link' id=backlinkpwres>back</button>
                                    </td><td></td></tr>
                                  </table>
                             </div>

                             <div class=hidden id=regform><br>
                                  <table border=0 cellspacing=0 cellpadding=0>
                                    <tr><td style='width: 90px;'><label class='control-label'>Your Name:</label></td><td><input type="text" id="regName" placeholder="Your Name"></td><td></td></tr>
                                    <tr><td style='width: 90px;'><label class='control-label'>E-mail:</label></td><td><input type="text" id="regEmail" placeholder="your@email"></td><td><div class="control-group error"><span id=regfailtext class="help-inline" style='margin-left: 10px'></span></div></td></tr>
                                    <tr><td></td><td><button class="btn btn-info" id=registerbtn2><i class='icon-pencil icon-white'></i> &nbsp;Register</button> <button class='btn btn-link' id=backlinkreg>back</button></td><td></td></tr>
                                  </table>
                             </div>

                          </div>
                       </div>
                    </div>

                    <div class=previewcontainer style='float: right;'><div style='position: absolute; top:0;' id=preview>preview</div></div>

                    <div id=presets style='margin-left: 20px;'>
                       <blockquote>Create New:</blockquote>
                       <div id=createnew style='margin-left: 20px;'></div>

                       <br clear=all><br>
                       <div class=hrline></div>
                       <blockquote>Open an example:</blockquote>
                       <div id=examples style='margin-left: 20px;'>
                       </div>
                    </div>

                    <div id=import class=hidden style='margin-left: 20px;'>
                       <form id=dlform action=dldata.php method=post target=dlframe style='margin: 0px;'><input type=hidden name=dldata id=dldata value=''><input type=hidden name=dldecode id=dldecode value=''><input type=hidden name=dlname id=dlname value=''></form>
                       <iframe id=dlframe name=dlframe src=blank.php style='display: none;'></iframe>
                       <canvas id="convertcanvas" width=200 height=200 style='display: none;'></canvas>

                       <blockquote>Import diagram from JSON file:</blockquote>
                         <div style='margin-left: 20px; margin-bottom: 20px;'>
                           <input type=file id=importfile class="filestyle" data-classButton="btn btn-primary" data-input="false">
                           <button id=importbutton class="hidden btn btn-success" type="button"><i class='icon-arrow-up icon-white'></i> &nbsp;Import</button><br>
                           <div id=importfilename style='margin-top: 5px; margin-left: 1px;'></div>
                         </div>

                       <div class="hrline"></div>
                       <blockquote>Export diagram in JSON format:</blockquote>
                         <div style='margin-left: 20px; margin-bottom: 20px;'>
                           <button class="btn" type="button" id=downloadjsonbtn><b style='font-size: 12px; position: relative; top: -1px; text-shadow: 1px 0 0 #000;'>{ }</b> &nbsp;Export JSON</button>
                           <span class="badge badge-warning actionscnt"></span>
                         </div>

                       <div class="hrline"></div>
                       <blockquote>Download diagram as image:</blockquote>
                         <div style='margin-left: 20px; margin-bottom: 20px;'>
                           <button class="btn" type="button" id=downloadsvgbtn><i class='icon-picture'></i> &nbsp;Download SVG image</button>
                           <span class="badge badge-warning actionscnt"></span> &nbsp;<div style='height: 10px;'></div>
                           <button class="btn" type="button" id=downloadpngbtn><i class='icon-picture'></i> &nbsp;Download PNG image</button>
                           <span class="badge badge-warning actionscnt"></span> &nbsp;
                         </div>

                       <div class="hrline"></div>
                       <blockquote>Download generated C code:</blockquote>
                         <div style='margin-left: 20px; margin-bottom: 20px;'>
                           <button id=downloadcodestaticbtn class='btn'><i class='icon-cog'></i>&nbsp; Download C code Static</button>
                           <span class="badge badge-warning actionscnt"></span> &nbsp;<div style='height: 10px;'></div>
                           <button id=downloadcodedynamicbtn class='btn'><i class='icon-cog'></i>&nbsp; Download C code Dynamic</button>
                           <span class="badge badge-warning actionscnt"></span> &nbsp;
                         </div>

                       <div class="hrline"></div>
                       <blockquote>Print diagram:</blockquote>
                         <div style='margin-left: 20px; margin-bottom: 20px;'>
                           <button class="btn" type="button" id=printsvgbtn><i class='icon-print'></i> &nbsp;Print</button>
                         </div>

                       <div class="hrline"></div>
                       <blockquote>Edit values which are assigned to transition identifiers:</blockquote>
                         <div style='margin-left: 20px; margin-bottom: 20px;'>
                           <button class="btn" type="button" id=idMapEditBtn><i class='icon-pencil'></i> &nbsp;Edit transition identifier mappings</button>
                           <textarea style='width: 90%; height: 200px;' class=hidden id=idMapEdit></textarea><br>
                           <button class="btn btn-success hidden" type="button" id=idMapSaveBtn><i class='icon-ok icon-white'></i> &nbsp;Save</button>
                         </div>

                       <div style='display: none;'>
                       <div class="hrline"></div>
                       <blockquote>Debug infos log, ignore this:</blockquote>
                         <div style='margin-left: 20px; margin-bottom: 20px;'>
                            <textarea style='border: 0; font-style: italic; width: 100%;' id=debug></textarea>
                         </div>
                       </div>

                    </div>



                    <div id=files class=hidden style='margin-left: 20px;'>
                       <div class=hiden id=loadarrow>
                          <img src=img/signinarrow.png width=146 height=141 class='pull-right' style='opacity: 0.5; margin-right: 45px; margin-top: -25px;'>
                          <blockquote>You have to sign in first</blockquote>
                       </div>

                       <div id=loadfiles>
                          <div class='pull-right tagfilter'><select style='width: 100px;' id=tagselect></select></div>
                          <div class='pull-right projsel'>Select: <a class=checkall>all</a>, <a class=checkall>none</a>, <a class=checkall>projectless</a></div>
                          <blockquote>Open Diagram: <span style='right: 10px;'></span></blockquote>
                          <div id=filesgrid style='margin-left: 20px;'>
                            <div id=modalfilelist></div>
                          </div>
                          <br clear=all><br>
                       </div>

                    </div>


                    <div id=saveas style='margin-left: 20px;' class="hidden">
                       <blockquote>Save current document as:</blockquote>
                         <div class="form-inline saveasdlg" style='margin-left: 20px; margin-bottom: 15px;'>
                            <table cellspacing=0 cellpadding=0 border=0 width="100%">
                               <tr>
                               <td width="100%" style='padding-right: 30px;'><input type=text placeholder="choose name" id=savename style='width: 100%; padding-left: 9px;'><input type=hidden id=saveid><br>&nbsp;</td>
                               <td style='padding-right: 15px;'><input type=text placeholder="Project name (optional)" id=saveproj data-suggest='tags' class=project style='padding-left: 9px; padding-right: 3px; width: 170px;'><br>&nbsp;</td>
                               <td><button id=savebutton class="btn btn-success" style='white-space: nowrap;' type="button"><i class='icon-hdd icon-white'></i> Save</button><br>&nbsp;&nbsp;<span class=hidden style='color: red;' id=unsaved>*unsaved</span></td>
                               <td style='padding-right: 15px;'></td>
                               <td><button id=commitbutton class="btn btn-success" style='white-space: nowrap;' type="button"><i class='icon-hdd icon-white'></i> Commit</button><br>&nbsp;&nbsp;</td>
                               </tr>
                            </table>
                        </div>
                        <div id=savelist class=savelist style='margin-left: 30px;'></div>
                    </div>


                    <table id=modalq class="hidden" border=0 cellspacing=0 cellpadding=0 style="position: fixed; left: 0; top: 0; width: 100%; height: 100%;">
                       <tr><td align=center>
                          <div id=modalqw class="radius5" style='background-color: #fff; padding: 30px; width: 550px; box-shadow: 0px 0px 10px #000000; max-height: 90%; overflow-y: auto;'></div>
                       </td></tr>
                    </table>


                    <div id=code style='margin-left: 20px;' class=hidden>
                       <table style='white-space: nowrap; background-color: #fff' border=0 cellspacing=0 cellpadding=0>
                       <tr><td valign=top>
                          <blockquote>Download:</blockquote>
                          <div style='margin-left: 20px;'>
                             <button id=downloadcodebtn class='btn btn-success'><i class='icon-download-alt icon-white'></i> Download Code as .zip</button> &nbsp; 
                          </div>
                       </td><td valign=top style='padding-left: 100px;'>
                          <blockquote>Memory Allocation:</blockquote>
                          <div class="btn-group" style='margin-left: 20px;' id=memalloc>
                            <button class="btn" id=memallocstaticbtn><i class="icon-ok icon-white hidden" id=memallocstatic></i> Static</button>
                            <button class="btn" id=memallocdynamicbtn><i class="icon-ok icon-white hidden" id=memallocdynamic></i> Dynamic</button>
                          </div>
                       </td>
                       </tr>
                       </table>

                       <br clear=all>
                       <div style='height: 1px; margin-left: -20px; margin-right: 0px; border-bottom: 1px dashed #eee; margin-bottom: 20px;'></div>

                       <div id=codegen>
                          <blockquote id=codegenHname></blockquote>
                          <pre class="sh_cpp" id=codegenHcode></pre><br>
                          <div style="height: 1px; margin-left: -20px; margin-right: 0px; border-bottom: 1px dashed #eee; margin-bottom: 20px;"></div>
                          <blockquote id=codegenCname></blockquote>
                          <pre class="sh_cpp" id=codegenCcode></pre><br>
                          <div style="height: 1px; margin-left: -20px; margin-right: 0px; border-bottom: 1px dashed #eee; margin-bottom: 20px;"></div>
                          <blockquote id=codegenMname></blockquote>
                          <pre class="sh_cpp" id=codegenMcode></pre>
                       </div>
                    </div>
                 </div>

              </td></tr>
           </table>
        </div>
     </td></tr>
   </table>
</div>

</div><!-- end of noprint section -->
<div class=print><img id=printelement></div>

<!-- instead of bluring all input/textarea/select elements, we will just focus this hidden one. This operation is way more lighter on CPU -->
<textarea id=blurator style='position: absolute; top:1px; left:1px; width: 1px; height: 1px; opacity: 0; z-index: 100'></textarea>

<script type="text/javascript" src="events.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="main.js<?php echo $rev; ?>"></script>
<script type="text/javascript" src="vars.php<?php echo $rev; ?>"></script>
