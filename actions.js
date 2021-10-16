
function importFilePrintError(text)
{
   $('#importfilename').html('<div style="color: #b94a48"><strong>Error:</strong> '+text+'</div>');
}

function importFileSelected()
{
   var name=$('#importfile').val().replace(/.*(\/|\\)/,'');

   if (!name.match(/[.]json$/i)) importFilePrintError('Please select a file ending with .json extension');
   else
   {
      $('#importfilename').html(name);

      var reader = new FileReader();
      reader.onloadend=function(ev)
      {
         g.import=this.result;
         try { var data=JSON.parse(g.import); } catch (err) { importFilePrintError('Not a valid JSON export file for FW Profile editor'); return; }
         try { var smName=data.globals.fwprop.smName; } catch(err) { importFilePrintError('Not a valid JSON export file for FW Profile editor'); return; }
         $('#importfilename').html("<b>Selected file:</b> "+name+"<br><b>Diagram name:</b> "+smName+"<BR><b>Type:</b> "+(data.globals.fwprop.editorType=='Pr' ? "Procedure" : "State Machine")+"<br><br><i>Click the Import button to proceed with import. Any unsaved diagram data will be lost.<br>You may still hit the Undo button to go back in history and revert the import though.</i>");
         $('#importbutton').show();
      };
      reader.readAsText($('#importfile')[0].files[0]);
   }
}

function importFileRun()
{
   historyAddPrepare();
   restoreFromExportString(g.import,true);
   modalDestroy();
   $('#importfile').val('');
   $('#importfile').change(function(){importFileSelected();});
   $('#importfilename').html('');
   $('#importbutton').hide();
   g.import='';
   historyAddFinish();
   refreshToolbars();
   msg('Import successful','info');
}


// update svg image, shift view so everything is visible, and fix some possible problems
function fixSVG(svg,viewbox)
{
   var vbx='0 0 800 400';
   if (viewbox)
   {
      var x=Math.floor(viewbox.x); if (isNaN(x)) x=0;
      var y=Math.floor(viewbox.y); if (isNaN(y)) y=0;
      var w=Math.floor(viewbox.width); if (isNaN(w)) w=0;
      var h=Math.floor(viewbox.height); if (isNaN(h)) h=0;
      if (w==0 || h==0) { w=800; h=400; }
      vbx='"'+x+" "+y+" "+w+" "+h+'"';
   }

   svg=svg.replace(/<desc[^>]*>[^<>]*<\/desc>/gi,"<desc>Created by FW Profile Editor thanks to Raphaeljs library</desc>");
   svg=svg.replace(/ *= */g,"=");
   svg=svg.replace(/viewBox="[^"]+"/gi,'viewBox='+vbx);
   if (!svg.match(/viewBox=/i)) svg=svg.replace(/(<svg )/i,"$1viewBox="+vbx+" ");
   svg=svg.replace(/(<svg [^>]*width=)"[^"]+"/gi,"$1"+'"'+w+'"');
   svg=svg.replace(/(<svg [^>]*height=)"[^"]+"/gi,"$1"+'"'+h+'"');
   svg=svg.replace(/&nbsp;/gi,"&#"+"160;");
   svg=svg.replace(/(<svg [^>]*(xmlns="[^"]+")[^>]*)\2/gi,"$1");
   svg=svg.replace(/(<svg [^>]*style=["'])/gi,"$1background-color: #eeeeee; ");

   if (!svg.match(/^<svg/i)) svg='<svg width="1px" height="1px" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink= "http://www.w3.org/1999/xlink"></svg>';
   return svg;
}


function getSVG(mime)
{
   var svg=fixSVG($("#desk").html(),getScreenBox());
   if (mime) return dataURL_encode(svg,mime);
   else return svg;
}


function dataURL_encode(str,mime)
{
   return "data:"+mime+";base64,"+base64_encode(str);
}


function dataURL_decode(str)
{
   return base64_decode(str.replace(/^.*?,/,""));
}


function base64_encode(str)
{
   return btoa(unescape(encodeURIComponent(str)));
}


function base64_decode(str)
{
   return decodeURIComponent(escape(atob(str)));
}

function JSON_fromString(str)
{
   var data={};
   try { data=JSON.parse(str); } catch(err) { return {}; }
   return data;
}

function JSON_toString(obj)
{
   return JSON.stringify(obj, null, 5);
}



function data2blob(data,isBase64)
{
   var chars="";
   if (isBase64) chars=atob(data); else chars=data;
   var bytes=new Array(chars.length);
   for (var i=0;i<chars.length; i++) bytes[i]=chars.charCodeAt(i);
   var blob=new Blob([new Uint8Array(bytes)]);
   return blob;
}



// Empty dlform, make it ready for a new file(s) download
function dlformReset()
{
   $('#dlform').empty();
   $('#dlformzipname').val('');
}

// filename to get if multiple files are downloaded - it's used for zip filename
function dlformZIPname(filename)
{
   $('#dlform').append("<input type=hidden name=zipname id=dlformzipname>");
   $('#dlformzipname').val(filename);
}

function dlformAppend(key,uniqFlag)
{
   if (uniqFlag) $('#dlform').append("<input type=hidden class=dlflags data-flag='"+uniqFlag+"'>");
   $('#dlform').append("<input type=hidden name=dlname"+key+" id=dlname"+key+" class=dlname data-key='"+key+"'>");
   $('#dlform').append("<input type=hidden name=dldata"+key+" id=dldata"+key+">");
   $('#dlform').append("<input type=hidden name=dldecode"+key+" id=dldecode"+key+">");
   $('#dlform').append("<input type=hidden name=dlfolder"+key+" id=dlfolder"+key+">");
}


function dlformSubmit()
{
   var zip=new JSZip();
   var key,name,folder,data,decode;
   var content,ret;
   var i=0;
   var files=$('.dlname');
   var filenames={};

   if (files.length==1)
   {
      key=$(files.get()).attr('data-key');
      name=$("#dlname"+key).val();
      data=$("#dldata"+key).val();
      decode=($("#dldecode"+key).val()=="true");
      if (decode) data=data.replace(/.*,/,"");
      ret=fileSaveAs(data2blob(data,decode), name);
   }
   else
   {
      files.each(function(i,el)
      {
         key=$(el).attr('data-key');
         name=$("#dlname"+key).val();
         folder=$("#dlfolder"+key).val();
         data=$("#dldata"+key).val();
         decode=($("#dldecode"+key).val()=="true");
         if (decode) data=data.replace(/.*,/,"");
         var filename=(folder?folder+"/":"")+name;
         if (filenames[filename]==1) filename=filename.replace(/(.*)[.]/,'$1 @'+key+'.'); else filenames[filename]=1;
         zip.file(filename, data, decode? {base64: true} :{} );
      });

      content=zip.generate({type:"blob",compression:"DEFLATE"});
      if (content) ret=fileSaveAs(content, $('#dlformzipname').val()+".zip");
   }

   // fallback to processing on server side if something fails locally
   if (!ret)
   {
      if (offline()) msg("Error in sending the download file. Try different browser.","error");
      else $('#dlform').submit();
   }
}


function downloadSVG()
{
   var i=0;
   dlformReset();

   for (var key in g.selectedFiles)
   {
      dlformAppend(key)
      $('#dldata'+key).val(findFileById(key).svg);
      $('#dlname'+key).val(findFileById(key).name+".svg");
      i++;
   }

   if (i<1) // no files selected, push current diagram data
   {
      dlformAppend('')
      $('#dldata').val(getSVG());
      $('#dlname').val(g.fwprop.smName+".svg");
   }
   else
      dlformZIPname("selected_"+i+"_svg_images");

   dlformSubmit();
}


function getImagePNGdata(image)
{
   var png=false;

   try
   {
      $("#convertcanvas").attr({'width':image.width,'height':image.height});
      var canvas=$("#convertcanvas").get(0);
      var context=canvas.getContext("2d");
      context.drawImage(image,0,0);
      png=canvas.toDataURL("image/png");
   } catch(e){}

   return png;
}


function downloadPNG()
{
   var i=0;
   dlformReset();

   for (var key in g.selectedFiles)
   {
      dlformAppend(key);
      $('#dldata'+key).val(getImagePNGdata($('#thumbimg'+key).get(0)));
      $('#dldecode'+key).val('true');
      $('#dlname'+key).val(findFileById(key).name+".png");
      i++;
   }

   if (i>0)
   {
      dlformZIPname("selected_"+i+"_png_images");
      dlformSubmit();
   }
   else
   {
      var image = new Image;
      image.onload = function()
      {
         dlformAppend('');
         $('#dldata').val(getImagePNGdata(image));
         $('#dldecode').val('true');
         $('#dlname').val(g.fwprop.smName+".png");
         dlformSubmit();
      }
      image.src = getSVG("image/svg+xml");
   }
}


function downloadJSON()
{
   var i=0;
   dlformReset();

   for (var key in g.selectedFiles)
   {
      dlformAppend(key);
      $('#dldata'+key).val(findFileById(key).fwprop);
      $('#dlname'+key).val(findFileById(key).name+".json");
      i++;
   }

   if (i<1) // no files selected, push current diagram data
   {
      dlformAppend('')
      $('#dldata').val(getExportString());
      $('#dlname').val(g.fwprop.smName+".json");
   }
   else
      dlformZIPname("selected_"+i+"_json_files");

   dlformSubmit();
}


function printSVG()
{
    $('#printelement').attr('src',getSVG("image/svg+xml"));
    window.print();
}
