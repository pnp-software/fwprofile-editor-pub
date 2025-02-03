<?php
   // Set locale so iconv() can work correctly

   setlocale(LC_ALL,"en_US");
   setlocale(LC_COLLATE,"C");

   function sendDownloadHeaders($name)
   {
      header("Content-Type: application/x-unknown");
      header("Content-Disposition: attachment; filename=".rawurlencode(trim($name)));
   }

   function nicePassword($length=8)
   {
       $w1='aeiou';
       $w2='bcdfghjklmnprstvxz';
       for ($i=1;$i<$length;$i++)
       {
          $pass.=$w2[mt_rand(0,strlen($w2)-1)].$w1[mt_rand(0,strlen($w1)-1)];
          $i++;
       }
       return $pass;
   }


   function debug($params)
   {
      $debug=debug_backtrace();
      foreach($params as &$param) $param=print_r($param,true);
      file_put_contents("/tmp/debug.txt",date("Y-m-d H:i:s")." - ".basename($debug[1]['file'])." - ".$debug[1]['function']."[".$debug[1]['line']."]".": ".join(", ",$params)."\n",FILE_APPEND);
   }


   // find out if user has access rights to diagram id $id
   // - he has if either the diagram is his, or if he has share rights
   // possible return values: ow (owner), rw (readwrite), ro (readonly)
   //
   function access_rights($diagramID)
   {
      $diagramID+=0;

      $result=execQuery("SELECT * FROM diagrams WHERE id=".$diagramID." and userID=".userID());
      if (mysqli_num_rows($result)>0) return 'ow'; // owner

      $email=get_user_email();
      $result=execQuery("SELECT perm FROM shares WHERE diagramID=".$diagramID." AND email=\"".mysqli_escape($email)."\"");
      $row=mysqli_fetch_assoc($result);
      return $row['perm'];
   }

   function get_user_email()
   {
      $email=@mysqli_result(execQuery("SELECT email FROM users WHERE id=".userID()),0);
      return $email;
   }

   function shared_diagram_ids()
   {
      $email=get_user_email();
      $ids=array();
      $result=execQuery("SELECT diagramID FROM shares WHERE email=\"".mysqli_escape($email)."\"");
      while($row=mysqli_fetch_assoc($result)) $ids[]=$row['diagramID'];
      return $ids;
   }

   function sharing_diagram_ids()
   {
      $ids=array();
      $result=execQuery("SELECT diagramID FROM diagrams LEFT JOIN shares ON shares.diagramID=diagrams.id WHERE diagrams.userID=".userID()." HAVING diagramID IS NOT NULL");
      while($row=mysqli_fetch_assoc($result)) $ids[]=$row['diagramID'];
      return $ids;
   }

?>
