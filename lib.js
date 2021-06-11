
   function reset(ar)
   {
      for (var i in ar) return ar[i];
   };


   // If current editor is set to edit State Machines, return valSM
   // If current editor is set to edit Procedures, return valPR
   // If current editor is set to edit something else, print message
   function editorSwitch(valSM, valPR, eType)
   {
      if (!eType) eType=g.fwprop.editorType;
      switch (eType)
      {
         case 'Sm':
            return valSM;
         case 'Pr':
            return valPR;
         default:
            msg("Current editor type is not set properly, that may indicate an internal application error. This should never happen. Further work may lead to undefined results.","error");
            return;
      }
   }

   // return true if editor is set to SM or PR respectively
   function editorIsSM(eType) { return editorSwitch(true,false,eType); }
   function editorIsPR(eType) { return editorSwitch(false,true,eType); }


   function htmlspecialchars(text)
   {
      text=text+"";

      var map = {
                  '&': '&amp;',
                  '<': '&lt;',
                  '>': '&gt;',
                  '"': '&quot;',
                  "'": '&#039;'
                };

      return text.replace(/[&<>"']/g, function(m) { return map[m]; }); //"
   }


   function extraSpacing(state,rad)
   {
      // this crazy stuff is just a quick approximation, needed to make the transition arrow to float nicely around choice pseudo state
      if (state.fwprop && state.fwprop.type=='choice') return 10-40*Math.abs(Math.sqrt(Math.abs(Math.sin(rad)*Math.cos(rad)))*Math.sin(rad)*Math.cos(rad));
      else return 0;
   }

   // returns an array of transition coordinates from the start to the end including intermediate vertexes
   function full_coordinates(conn)
   {
      var coord=[];
      var i=0;
      var spacing=5;
      var arrowsize=3;
      var start,end,rad;

      var s1=conn.stateFrom;
      var s2=conn.stateTo;

      var x1=s1.attr("x")-spacing,
          y1=s1.attr("y")-spacing,
          x2=s2.attr("x")-spacing-arrowsize,
          y2=s2.attr("y")-spacing-arrowsize,
          w1=s1.attr("width")+2*spacing,
          h1=s1.attr("height")+2*spacing,
          w2=s2.attr("width")+2*spacing+2*arrowsize,
          h2=s2.attr("height")+2*spacing+2*arrowsize;

      if (conn.movingCoord && !$.isEmptyObject(conn.movingCoord))
      {
         if (conn.movingEnd)
         {
            x2=conn.movingCoord.x;
            y2=conn.movingCoord.y;
            w2=0;
            h2=0;
         }
         else
         {
            x1=conn.movingCoord.x;
            y1=conn.movingCoord.y;
            w1=0;
            h1=0;
         }
      }

      if (conn.vertexes.length>0)
      {
         rad=Raphael.rad(Raphael.angle(x1+w1/2,y1+h1/2,conn.vertexes[0].x,conn.vertexes[0].y));
         start = angle_spot(x1 - extraSpacing(s1,rad), y1 - extraSpacing(s1,rad), w1 + 2*extraSpacing(s1,rad), h1 + 2*extraSpacing(s1,rad), (rad + Math.PI) % (2 * Math.PI));
         rad=Raphael.rad(Raphael.angle(conn.vertexes[conn.vertexes.length-1].x,conn.vertexes[conn.vertexes.length-1].y,x2+w2/2,y2+h2/2));
         end = angle_spot(x2 - extraSpacing(s2,rad), y2 - extraSpacing(s2,rad), w2 + 2*extraSpacing(s2,rad), h2 + 2*extraSpacing(s2,rad), rad);
      }
      else
      {
          // if we are not using any vertexes, join objects from centers if they overlap or are too close
          if ( (x2 >= x1 && x2 <= x1+w1 && y2 >= y1 && y2 <= y1+h1) ||
               (x2+w2 >= x1 && x2+w2 <= x1+w1 && y2 >= y1 && y2 <= y1+h1) ||
               (x2+w2 >= x1 && x2+w2 <= x1+w1 && y2+h2 >= y1 && y2+h2 <= y1+h1) ||
               (x2 >= x1 && x2 <= x1+w1 && y2+h2 >= y1 && y2+h2 <= y1+h1) ||
               (x1 >= x2 && x1 <= x2+w2 && y1 >= y2 && y1 <= y2+h2) ||
               (x1+w1 >= x2 && x1+w1 <= x2+w2 && y1 >= y2 && y1 <= y2+h2) ||
               (x1+w1 >= x2 && x1+w1 <= x2+w2 && y1+h1 >= y2 && y1+h1 <= y2+h2) ||
               (x1 >= x2 && x1 <= x2+w2 && y1+h1 >= y2 && y1+h1 <= y2+h2) )
          {
             x1=x1+w1/2; y1=y1+h1/2; x2=x2+w2/2; y2=y2+h2/2;
             w1=1; h1=1; w2=1; h2=1;
          }

          rad=Raphael.rad(Raphael.angle(x1+w1/2,y1+h1/2,x2+w2/2,y2+h2/2));
          start = angle_spot(x1 - extraSpacing(s1,rad), y1 - extraSpacing(s1,rad), w1 + 2*extraSpacing(s1,rad), h1 + 2*extraSpacing(s1,rad), (rad + Math.PI) % (2 * Math.PI));
          end = angle_spot(x2 - extraSpacing(s2,rad), y2 - extraSpacing(s2,rad), w2 + 2*extraSpacing(s2,rad), h2 + 2*extraSpacing(s2,rad), rad);
      }

      coord.push({x: start[0], y: start[1]});
      for (i=0; i<conn.vertexes.length; i++) coord.push({x: conn.vertexes[i].x, y: conn.vertexes[i].y});
      coord.push({x: end[0], y: end[1]});

      return coord;
   }


   function arrow_path_string()
   {
      return "M10,-1L0,5L10,11L7,6L7,4z"
   }

   // calculate spot on element which is going to be starting / ending point of transition path
   function angle_spot(x, y, w, h, rad)
   {
       var cang = Math.atan(h/w);
       var dxy = new Array(2);

       if (rad > 2*Math.PI-cang || rad < cang)
       {
           dxy[0] = x + w;
           dxy[1] = y + h/2 + Math.tan(rad) * w/2;
       }
       else if (rad >= cang && rad <= (Math.PI - cang))
       {
           dxy[0] = x + w/2 + Math.tan(Math.PI / 2 - rad) * h/2;
           dxy[1] = y + h;
       }
       else if (rad > (Math.PI - cang) && rad < (Math.PI + cang))
       {
           dxy[0] = x;
           dxy[1] = y + h/2 - Math.tan(rad) * w/2;
       }
       else
       {
           dxy[0] = x + w/2 - Math.tan(Math.PI/2 - (rad - Math.PI)) * h/2;
           dxy[1] = y;
       }

       return dxy;
   }

   // ------------------------------------
   // functions for calculation of length on transtition arrow on mouseover
   // ------------------------------------

   // linear algebra projection needs a line which is 90 degrees rotated of the original direction
   function rotateOrigin(x,y,angle)
   {
      var rotxy = new Array();
      var radangle = Raphael.rad(angle);
      rotxy[0] = Math.cos(radangle)*x+Math.sin(radangle)*y;
      rotxy[1] = Math.cos(radangle)*y-Math.sin(radangle)*x;
      return rotxy;
   }

   // returns best approximation of length of given zigzag line (defined by dots) to the x/y mouse coordinates
   function getLengthAtPoint(dots, x, y)
   {
      var diff = 10000;
      var xsect_d = new Array();
      var length = 0;
      var length_und = 0;

      for(var n = 0; n < dots.length-1; n++)
         xsect_d[n] = new Array();

      for(var a = 0; a < dots.length-1; a++)
      {
         var angle = Raphael.angle(dots[a+1].x,dots[a+1].y,dots[a].x,dots[a].y);
         var pt1 = rotateOrigin(dots[a].x,dots[a].y,angle);
         var pt2 = rotateOrigin(x,y,angle);
         var xsect = rotateOrigin(pt2[0],pt1[1],-1*angle);

         if(!(Math.max(dots[a].x,dots[a+1].x) +.1 >= xsect[0]  && Math.min(dots[a].x,dots[a+1].x) -.1 <= xsect[0] && Math.max(dots[a].y,dots[a+1].y) +.1  >= xsect[1] && Math.min(dots[a].y,dots[a+1].y) -.1 <= xsect[1]))
         {
            var dist1 = Math.sqrt(Math.pow(dots[a].x-x,2)+Math.pow(dots[a].y-y,2));
            var dist2 = Math.sqrt(Math.pow(dots[a+1].x-x,2)+Math.pow(dots[a+1].y-y,2));
            if(dist1 < dist2)
            {
               xsect[0] = dots[a].x;
               xsect[1] = dots[a].y;
            }
            else
            {
               xsect[0] = dots[a+1].x;
               xsect[1] = dots[a+1].y;
            }
         }
         xsect_d[a][0] = xsect[0];
         xsect_d[a][1] = xsect[1];
         xsect_d[a][2] = Math.sqrt(Math.pow(xsect[0]-x,2)+Math.pow(xsect[1]-y,2));
      }

      for(var t = 0; t < dots.length-1; t++)
      {
         length_und += Math.sqrt(Math.pow(dots[t].x-xsect_d[t][0],2)+Math.pow(dots[t].y-xsect_d[t][1],2));
         if(xsect_d[t][2] < diff)
         {
            length += length_und;
            length_und = 0;
            diff = xsect_d[t][2];
         }
         length_und += Math.sqrt(Math.pow(dots[t+1].x-xsect_d[t][0],2)+Math.pow(dots[t+1].y-xsect_d[t][1],2));
      }
      return length;
   }



   function str_repeat(s,n)
   {
      // way faster than for() cycle
      return new Array(Math.floor(n+1)).join(s);
   }



   // -------------------------------------------
   //
   //   new state element
   //
   // -------------------------------------------

   function newstate(type,x,y)
   {
       var n,m; var bbox; end=false;
       x+=g.paperPanX;
       y+=g.paperPanY;
       var nextID=getNextStateAutoID(type);

       switch (type)
       {
           case "state":
              n=g.states.push(paper.rect(x,y, 100, editorSwitch(100,30), 10).attr({fill: "#fff", "stroke-width": 1, "stroke": "#666", "fill-opacity": 100 }).transform("t0.5,0.5"));
              break;

           case "choice":
              n=g.states.push(paper.rect(x,y, editorSwitch(30,24),editorSwitch(30,24)).attr({fill: "#fff", "stroke-width": 1, "stroke": "#666", "fill-opacity": 100 }).transform("r45t0.5,0.5"));
              break;

           case "init":
              n=g.states.push(paper.rect(x,y,30,30,15).attr({fill: "#fff", "stroke-width": 1, "stroke": "#666", "fill-opacity": 100 }).transform("t0.5,0.5"));
              break;

           case "final":
              n=g.states.push(paper.rect(x,y,26,26,13).attr({fill: "#000", "stroke-width": 1, "stroke": "#000", "fill-opacity": 100 }).transform("t0.5,0.5"));
              break;

           case "note":
              n=g.states.push(paper.rect(x,y, 100, 33, 0).attr({fill: "#fff", "stroke-width": 1, "stroke": "#666", "fill-opacity": 100, 'stroke-dasharray': '--' }).transform("t0.5,0.5"));
              break;

           case "notedot":
              n=g.states.push(paper.rect(x,y,6,6,6).attr({fill: "#000", "stroke-width": 1, "stroke": "#000", "fill-opacity": 100 }).transform("t0.5,0.5"));
       }


       g.states[n-1].fwprop={};
       g.states[n-1].fwprop.autoid=nextID;
       g.states[n-1].fwprop.type=type;
       g.states[n-1].fwprop.note="";
       g.states[n-1].parentState=false;

       switch (type)
       {
          case "state":

             g.states[n-1].text=paper.text(x,y, "").attr({"font-size": 10, "cursor": "default", "text-anchor":"start"});
             g.states[n-1].text.parent=g.states[n-1];
             g.states[n-1].text.drag(statedragmove,statedragstart,statedragup);
             g.states[n-1].text.dblclick(editboxOn);

             if (editorIsSM())
             {
                g.states[n-1].underline=paper.path("M0,0").attr({"fill":"#000"});
                g.states[n-1].underline.parent=g.states[n-1];
                g.states[n-1].underline.drag(statedragmove,statedragstart,statedragup);
                g.states[n-1].underline.dblclick(editboxOn);
             }

             g.states[n-1].fwprop.identifier=editorSwitch("STATE","NODE")+nextID;
             g.states[n-1].fwprop.entryFunc="";
             g.states[n-1].fwprop.doFunc="";
             g.states[n-1].fwprop.exitFunc="";
             g.states[n-1].fwprop.entryType="function";
             g.states[n-1].fwprop.doType="function";
             g.states[n-1].fwprop.exitType="function";
             g.states[n-1].fwprop.entryCode="";
             g.states[n-1].fwprop.doCode="";
             g.states[n-1].fwprop.exitCode="";
             g.states[n-1].fwprop.entryDesc="";
             g.states[n-1].fwprop.doDesc="";
             g.states[n-1].fwprop.exitDesc="";
             updateStateText(g.states[n-1]);
             break;

           case "choice":
              g.states[n-1].fwprop.identifier=editorSwitch("CHOICE","DECISION")+nextID;
              break;

           case "init":
              break;

           case "final":
              break;

           case "note":
              g.states[n-1].text=paper.text(x,y, "").attr({"font-size": 10, "cursor": "default", "text-anchor":"start"});
              g.states[n-1].text.parent=g.states[n-1];
              g.states[n-1].text.drag(statedragmove,statedragstart,statedragup);
              g.states[n-1].text.dblclick(editboxOn);
              updateStateText(g.states[n-1]);
              break;
       }

       g.states[n-1].drag(statedragmove,statedragstart,statedragup);
       g.states[n-1].dblclick(editboxOn);
       g.states[n-1].mousemove(statemousemove);

       return g.states[n-1];
   }


   function addNoteConnector()
   {
      if (g.selected.length!=1) return;
      var state=g.selected[0];
      if (!stateIsNote(state)) return;
      var dot=newstate('notedot',state.attr("x")+Math.floor(Math.random()*state.attr("width")),state.attr("y")+state.attr("height")+50+Math.floor(Math.random()*50));
      connect(state,dot);
   }


   function filterOutRemovedStates() // remove from states array all empty (deleted) states
   {
      var i;
      for (i=0; i<g.states.length; i++)
      {
         if (g.states[i].removed)
           g.states.splice(i--,1);
      }
   }


   function getConnectionsIndex(obj)
   {
      var i;
      for (i=0; i<g.connections.length; i++)
         if (g.connections[i].id===obj.id) return i;
      return false;
   }


   function destroySelectors()
   {
       if (g.connector) { g.connector.remove(); g.connector=false; }
       if (g.connectorArrow) { g.connectorArrow.arrowend.remove(); g.connectorArrow.remove(); g.connectorArrow=false; }
       if (g.enlarger) { g.enlarger.remove(); g.enlarger=false; }
   }

   function deleteConnection(i)
   {
       reorderTransitions(g.connections[i].stateFrom, g.connections[i].fwprop.order, countOutgoingTransitions(g.connections[i].stateFrom) );
       g.connections[i].arrowend.remove();
       g.connections[i].text.remove();
       g.connections[i].remove();

       g.connections.splice(i,1);
       g.selectedCon=false;
   }

   // function optimized to delete all states and connections at once
   function deleteAllStates()
   {
      var i;

      for(i=0;i<g.states.length;i++)
      {
         if (g.states[i].text) g.states[i].text.remove();
         if (g.states[i].underline) g.states[i].underline.remove();
         g.states[i].remove();
      }

      for(i=0;i<g.connections.length;i++)
      {
         g.connections[i].text.remove();
         g.connections[i].arrowend.remove();
         g.connections[i].remove();
      }

      g.states=[];
      g.connections=[];
      g.selected=[];
      g.selectedCon=false;
   }

   function deleteState(state)
   {
      // make sure there is nothing left
      destroySelectors();

      var dots=findConnectionTargets(state);

      var i;
      for (i=0; i<g.connections.length; i++)
        if (g.connections[i].stateFrom.id===state.id || g.connections[i].stateTo.id===state.id)
           deleteConnection(i--);

      if (state.text) state.text.remove();
      if (state.underline) state.underline.remove();

      if (stateIsNote(state) && dots.length>0)
         for (i=0; i<dots.length; i++)
           dots[i].remove();

      state.remove();
      filterOutRemovedStates();
      refreshConnections();
   }


   function deleteSelectedStates()
   {
      var i;
      for (i=0; i<g.selected.length; i++)
         deleteState(g.selected[i]);

      g.selected=[];
      refreshToolbars();
   }

   function deleteSelectedConnection()
   {
      if (g.selectedCon)
      {
          if (stateIsNoteDot(g.selectedCon.stateTo)) deleteState(g.selectedCon.stateTo);
          else deleteConnection(getConnectionsIndex(g.selectedCon));
      }
      refreshConnections();
      refreshToolbars();
   }


   function connectionIndex(s1,s2)
   {
      var i;
      for (i=0; i<g.connections.length; i++)
      {
         if (g.connections[i].stateFrom.id===s1.id && g.connections[i].stateTo.id===s2.id)
         return i;
      }
      return false;
   }

   function isConnected(s1,s2)
   {
      return connectionIndex(s1,s2)!==false;
   }


   function findConnectionSource(target)
   {
      var i;
      for (i=0; i<g.states.length; i++)
         if (isConnected(g.states[i],target))
            return g.states[i];
      return false;
   }

   function findConnectionTargets(source)
   {
      var i;
      var ret=[];
      for (i=0; i<g.states.length; i++)
         if (isConnected(source,g.states[i]))
            ret.push(g.states[i]);
      return ret;
   }

   function connectionsCountFrom(state)
   {
      var i, res=0;
      for (i=0; i<g.connections.length; i++)
         if (g.connections[i].stateFrom.id==state.id) res++;
      return res;
   }


   // -------------------------------------------
   // connect one state with other one by an arrow
   // -------------------------------------------
   function connect(s1,s2,tshiftx,tshifty,verts,sh)
   {
        if (editorIsSM())
        {
           if (s2.fwprop.type=='init') { msg("Initial state doesn't accept incomming transitions","error"); return false; }
           if (s2.fwprop.type==s1.fwprop.type && s1.fwprop.type=='choice') { msg("The source and target of a transition cannot both be choice pseudo-states","error"); return false; }
           if (s1.fwprop.type=='init' && connectionsCountFrom(s1)>0) { msg("Only one transition is allowed from initial state","error"); return false; }
           if (s1.fwprop.type=='init' && s2.fwprop.type=='final') { msg("Transition from initial to final state makes no sense","error"); return false; }
        }

        if (editorIsPR())
        {
           if (s2.fwprop.type=='init') { msg("Initial node doesn't accept incomming control flow","error"); return false; }
           if (s1.fwprop.type=='init' && connectionsCountFrom(s1)>0) { msg("Only one control flow is allowed from initial node","error"); return false; }
           if (s1.fwprop.type=='state' && connectionsCountFrom(s1)>0) { msg("Only one control flow is allowed from action node","error"); return false; }
           if (s1.fwprop.type=='init' && s2.fwprop.type=='final') { msg("Transition from initial to final node makes no sense","error"); return false; }
        }

        var order=getNextOrderID(s1);

        var m=g.connections.push(paper.path("M0,0").attr({"stroke-width": 2, "stroke-linecap": "round", "stroke-linejoin": "round", color: '#000'})); // dummy path, will be updated later
        g.connections[m-1].stateFrom=s1;
        g.connections[m-1].stateTo=s2;
        g.connections[m-1].coord=[]; // last known coordinates for full transition path, in {x,y} pairs
        if (!verts) verts=[];
        g.connections[m-1].vertexes=verts; // relative coordinates of points through which the arrow passes, array of {x,y} pairs
        if (!sh) sh={x:0,y:0};
        g.connections[m-1].shiftxy=sh; // relative shift of connection arrow

        g.connections[m-1].fwprop={};
        g.connections[m-1].fwprop.order=order;

        g.connections[m-1].fwprop.identifier= editorSwitch( s1.fwprop.type=='state' ? "Trigger"+getNextTriggerAutoID() : "" ,"");
        g.connections[m-1].fwprop.guardFunc = editorSwitch( s1.fwprop.type=='choice' ? 'Guard'+getNextGuardAutoID() : ""    ,"");
        g.connections[m-1].fwprop.actionFunc= editorSwitch( s1.fwprop.type=='init' ? 'Action'+getNextActionAutoID() : ""    ,"");

        g.connections[m-1].fwprop.guardType="function";
        g.connections[m-1].fwprop.actionType="function";
        g.connections[m-1].fwprop.guardCode="";
        g.connections[m-1].fwprop.actionCode="";

        // make connection's label somewhere on the paper, coordinates will update later
        g.connections[m-1].shiftx= tshiftx ? tshiftx : 0;
        g.connections[m-1].shifty= tshifty ? tshifty : 0;

        g.connections[m-1].arrowend=paper.path(arrow_path_string()).attr({color: '#000', fill: '#000'});
        g.connections[m-1].arrowend.parentPath=g.connections[m-1];
        g.connections[m-1].arrowend.mousemove(mouseOverArrow);
        g.connections[m-1].arrowend.drag(shifterdragmove,shifterdragstart,shifterdragend);
        g.connections[m-1].arrowend.dblclick(editboxOn);

        g.connections[m-1].text=paper.text(0,0,"").attr({"cursor": "move"});
        g.connections[m-1].text.parent=g.connections[m-1];

        g.connections[m-1].drag(labeldragmove,labeldragstart,labeldragup); // to handle click for selection
        g.connections[m-1].dblclick(editboxOn);
        g.connections[m-1].mouseover(mouseOverArrow);
        g.connections[m-1].mousemove(mouseOverArrow);
        g.connections[m-1].text.drag(labeldragmove,labeldragstart,labeldragup);
        g.connections[m-1].text.dblclick(editboxOn);

        // now update the connection on screen
        refreshConnection(m-1);
        return g.connections[m-1];
   }


   function selfConnect(state)
   {
      var i;
      if (!state)
      {
         for(i=0;i<g.selected.length;i++)
            if (g.selected[i].fwprop.type=='state')
            {
               state=g.selected[i];
               break;
            }
      }

      if (!state) return;
      var X=state.attr("x"),
          Y=state.attr("y");

      connect(state,state,20,-15,[{x:X-20,y:Y+10},{x:X+10,y:Y-20}]);
   }


   function refreshBackground()
   {
      paper.setViewBox(g.paperPanX,g.paperPanY, paper.width*g.zoom, paper.height*g.zoom, false);
      g.bg.attr({x: g.paperPanX, y: g.paperPanY, width: paper.width, height: paper.height});
   }

   function paperScroll(e)
   {
      var delta = e.type=="mousewheel" ? e.originalEvent.wheelDelta : -e.originalEvent.detail;
      g.paperPanY -= delta > 0 ? 60 : -60;
      refreshBackground();
   }

   function doZoom(zoom,x,y)
   {
      g.paperPanX-=x-x*g.zoom;
      g.paperPanY-=y-y*g.zoom;
      g.zoom=zoom;
      g.paperPanX+=x-x*g.zoom;
      g.paperPanY+=y-y*g.zoom;
      if (g.selectionbox) g.selectionbox.attr({opacity: zoom/8});
      refreshBackground();
   }


   function snap_area(snapx,snapy, reqcx,reqcy, snap)
   {
      if (!snap) snap=10;
      var x=0,y=0;
      if (Math.abs(snapx-reqcx)<snap) x=snapx-reqcx;
      if (Math.abs(snapy-reqcy)<snap) y=snapy-reqcy;
      return [x,y];
   }

   function build_path_string(dots)
   {
       var path = "";
       for (var i=0; i<dots.length;i++) path = path + (i<1 ? "M" : "L") + dots[i].x + "," + dots[i].y;
       return path;
   }

   function lastPoint(coord,dx,dy)
   {
      return (coord[coord.length-1].x+dx)+","+(coord[coord.length-1].y+dy);
   }

   function lastPointAngle(coord)
   {
      return Raphael.angle(coord[coord.length-2].x,coord[coord.length-2].y,coord[coord.length-1].x,coord[coord.length-1].y);
   }


   function window_open(url,id,forceExternal)
   {
      if (isChromeApp() && !forceExternal)
      {
         // open help in a single window all the time
         if (id=='help')
         {
            chrome.app.window.create(url,{"id":"help","bounds":{"width":window.screen.availWidth/2,"height":window.screen.availHeight/2}},
                function(w) { if (w.contentWindow.gotoHash) w.contentWindow.gotoHash(url.replace(/.*#/,"#")); }
            );
            return;
         }

         // check if a window with given id hash already exists
         var allWindows=chrome.app.window.getAll();
         for(var i=0; i<allWindows.length; i++)
            if (allWindows[i].contentWindow.location.hash==url)
               return allWindows[i].show();

         // if we are so far, document is not open yet. Open it now.
         return chrome.app.window.create((url.match("/")?url:"index.html"+url),{"id":"w"+(new Date).getTime(),"bounds":{"width":window.screen.availWidth,"height":window.screen.availHeight}});
      }
      else
         return window.open(url,id);
   }

   function openHelp(hash)
   {
      window_open('./doc/'+(offline()?"index.html":"")+(hash?"#"+hash:"#"),'help');
   }

   function deleteKeyPressed()
   {
      historyAddPrepare();
      deleteSelectedStates();
      deleteSelectedConnection();
      historyAddFinish();
      refreshToolbars();
   }

   function updateConnectionText(conn, color)
   {
      if (!conn.fwprop) return;
      if (stateIsNote(conn.stateFrom)) return;

      stereotypeAp = svgDoubleLt()+"AP"+svgDoubleGt()+" ";
      sStereotypeApGuard = conn.fwprop.guardAp?stereotypeAp:"";
      sStereotypeApAction = conn.fwprop.actionAp?stereotypeAp:"";

      var text= (conn.fwprop.identifier!='' ? conn.fwprop.identifier : "") +
                ( (conn.fwprop.guardFunc && conn.fwprop.guardFunc!='')
                  || (conn.fwprop.guardCode && conn.fwprop.guardCode!='')
                  || (conn.fwprop.guardDesc && conn.fwprop.guardDesc!='') ? " [ "+sStereotypeApGuard+svgNewlines(displayInfoText(conn.fwprop.guardFunc?conn.fwprop.guardFunc:conn.fwprop.guardCode,conn.fwprop.guardDesc))+" ] " : "");

          text = text + ( (conn.fwprop.actionFunc && conn.fwprop.actionFunc!='')
                          || (conn.fwprop.actionCode && conn.fwprop.actionCode!='')
                          || (conn.fwprop.actionDesc && conn.fwprop.actionDesc!='') ? " / " + sStereotypeApAction+svgNewlines(displayInfoText(conn.fwprop.actionFunc?conn.fwprop.actionFunc:conn.fwprop.actionCode,conn.fwprop.actionDesc)) : "");

      if (countOutgoingTransitions(conn.stateFrom)>1 && g.fwprop.displayOrder) text = "" + conn.fwprop.order+": "+text;

      if (conn.text)
      {
         if (color) conn.text.attr({ "fill": color });
         if (conn.text.str!=text) // update the text only if it differs, it's CPU intensive
         {
            conn.text.str=text;
            conn.text.attr({ "text": text });
         }
      }
   }


   function arrowend_refresh(conn)
   {
      conn.arrowend.transform("t"+lastPoint(conn.coord,0,-5)+"r"+lastPointAngle(conn.coord)+",0,5t-4.5,0");
   }


   function updateConnectionAttrs(conn, color)
   {
      var sx=0; var sy=0;
      conn.coord=full_coordinates(conn);

      // shift all coordinates
      if (conn.shiftxy)
      for (var i=0; i<conn.coord.length; i++)
      {
         conn.coord[i].x+=conn.shiftxy.x;
         conn.coord[i].y+=conn.shiftxy.y;
      }

      conn.attr( { path: build_path_string(conn.coord) } );
      arrowend_refresh(conn);
      conn.attr({'stroke-dasharray': ""});

      if (stateIsNote(conn.stateFrom))
      {
         conn.attr({'stroke-dasharray': "-"});
         conn.arrowend.hide();
      }

      if (color)
      {
         conn.attr({stroke: color});
         conn.arrowend.attr({stroke: color, fill: color});
      }
      updateConnectionText(conn,color);
   }

   function refreshConnection(i, color)
   {
      if (!g.connections || i===false || !g.connections[i]) return;

      var border=2;
      var shiftx=g.connections[i].shiftx ? g.connections[i].shiftx : 0;
      var shifty=g.connections[i].shifty ? g.connections[i].shifty : 0;

      updateConnectionAttrs(g.connections[i],color);

      var middle=g.connections[i].getPointAtLength(g.connections[i].getTotalLength()/2);
      var newx=Math.floor(middle.x + shiftx);
      var newy=Math.floor(middle.y -10 + shifty);
      if (g.connections[i].text.attr("x")!=newx || g.connections[i].text.attr("y")!=newy) // update only if differs
      {
         g.connections[i].text.attr({ x: newx, y: newy })
      }
   }


   function updateTitle()
   {
      var t="FW Profile Editor for State Machines and Procedures";
      if (g.fwprop.smName!='') t=g.fwprop.smName+" :: "+editorSwitch("State Machine","Procedure");
      document.title=t;
   }

   function refreshConnections(obj)
   {
      for (var i=0; i<g.connections.length; i++)
      {
         if (!obj || g.connections[i].stateFrom.id===obj.id || g.connections[i].stateTo.id===obj.id)
           refreshConnection(i);
      }
   }

   function refreshState(state)
   {
      if (state.text) updateStateText(state);
      if (g.connector) g.connector.attr({x:state.attr("x")+state.attr("width")+(state.attr("height")>40?-5:-15),y: state.attr("y")+(state.attr("height")>40?15:-5)});
      if (g.enlarger) g.enlarger.transform("t"+(state.attr("x")+state.attr("width")-9-0.5)+","+(state.attr("y")+state.attr("height")+0.5));
   }

   function refreshStates()
   {
      for (var i=0; i<g.states.length; i++)
         refreshState(g.states[i]);
   }


   function stateIsNote(state)
   {
      if (state.fwprop && state.fwprop.type=='note') return true;
      return false;
   }

   function stateIsNoteDot(state)
   {
      if (state.fwprop && state.fwprop.type=='notedot') return true;
      return false;
   }

   // return javascript character 160, which is unicode's nonbreaking blank space
   function svgEmptyChar()
   {
      return String.fromCharCode(160);
   }

   function svgDoubleLt()
   {
       return String.fromCharCode(60).concat(String.fromCharCode(60));
   }
   function svgDoubleGt()
   {
       return String.fromCharCode(62).concat(String.fromCharCode(62));
   }   

   function textPFX(text,prefix,suffix,nonbsp)
   {
      if (text && text.replace(/\s/g,'')!='') return (prefix?prefix:"")+text+(suffix?suffix:"");
      else if (!nonbsp) return svgEmptyChar();
      return "";
   }

   function svgNewlines(text,padchars)
   {
      if (padchars) padchars = new Array(padchars).join(svgEmptyChar()); else padchars='';
      if (isEmpty(text)) return "";
      return text.replace(/\n\n/g,svgEmptyChar()+"\n"+svgEmptyChar()+"\n").replace(/\n/g,"\n"+padchars);
   }

   function displayInfoText(fnc,desc)
   {
      if (g.fwprop.displayInfo==1) return isEmpty(fnc)?"":fnc;
      else if (g.fwprop.displayInfo==2) return isEmpty(desc)?"":desc;
      else
      {
         if (!isEmpty(desc)) return desc;
         else if (!isEmpty(fnc)) return fnc;
      }
      return "";
   }

   function buildStateText(state)
   {
      if (stateIsNote(state)) return svgNewlines(state.fwprop.note);

      stereotypeAp = svgDoubleLt()+"AP"+svgDoubleGt();

      if (editorIsSM())
      {
          sStereotypeApEntry = state.fwprop.entryAp?stereotypeAp:svgEmptyChar();
          sStereotypeApDo = state.fwprop.doAp?stereotypeAp:svgEmptyChar();
          sStereotypeApExit = state.fwprop.exitAp?stereotypeAp:svgEmptyChar();

         var ret=svgEmptyChar()+textPFX(state.fwprop.identifier)+svgEmptyChar()+"\n"
                +svgEmptyChar("")+"\n"
                +svgEmptyChar("")+"\n"
                +textPFX(fileNameByID(state.fwprop.embedSmId),svgEmptyChar()+'eSM: ',"\n",true)
                +textPFX(sStereotypeApEntry+displayInfoText(state.fwprop.entryFunc?state.fwprop.entryFunc:svgNewlines(state.fwprop.entryCode,15),svgNewlines(state.fwprop.entryDesc,15)),svgEmptyChar("")+'Entry: ',"\n",true)
                +textPFX(sStereotypeApDo+displayInfoText(state.fwprop.doFunc?state.fwprop.doFunc:svgNewlines(state.fwprop.doCode,15),svgNewlines(state.fwprop.doDesc,15)),svgEmptyChar("")+'Do: ',"\n",true)
                +textPFX(sStereotypeApExit+displayInfoText(state.fwprop.exitFunc?state.fwprop.exitFunc:state.fwprop.exitCode,svgNewlines(state.fwprop.exitDesc,15)),svgEmptyChar("")+'Exit: ',"\n",true);

         return ret.replace(new RegExp("("+svgEmptyChar("")+"\n)+"+'$'), '');
      }

      if (editorIsPR())
      {
          sStereotype = state.fwprop.entryAp?stereotypeAp:svgEmptyChar();

          return textPFX(state.fwprop.identifier)+": "+sStereotype
             +textPFX(displayInfoText(state.fwprop.entryFunc?state.fwprop.entryFunc:state.fwprop.entryCode,svgNewlines(state.fwprop.entryDesc,2)),svgEmptyChar(),"",true);
      }
   }


   function updateStateBoxByText(state,margin,onlyWidth)
   {
      var w=Math.floor(state.text.getBBox().width/2)*2;
      var h=Math.floor(state.text.getBBox().height/2)*2;

      if (w+margin>state.attr("width"))
      {
         state.attr({ "width": w+margin });
         refreshConnections();
      }

      // onlyWidth is needed to workaround some glitch when height is sometimes returned bigger on subsequent calls in one run
      if (onlyWidth) return;

      if (h+margin>state.attr("height"))
      {
         state.attr({ "height": h+margin });
         refreshConnections();
      }
      return h;
   }


   function updateStateText(state,updateOnlyPosition)
   {
      var margin=20;

      if (state.text)
      {
         if (!updateOnlyPosition)
         {
            state.text.attr("text", buildStateText(state));
         }
         
         // force pre-formated whitespace for Notes
         if (stateIsNote(state)) $(state.text.node).css("white-space","pre");
         
         var y=0
         var tspans=state.text.node.childNodes;

         if (tspans)
         {
            $(tspans[0]).css("font-weight","bold");
            y=$(tspans[0]).attr("dy");
         }

         var h=updateStateBoxByText(state,margin);
         state.text.attr({ x:state.attr("x")+(margin/2), y: state.attr("y")+(stateIsNote(state)?state.attr("height")/2:(h+margin)/2) });

         var words=[];
         var tspan;

         if (tspans)
         {
            for (var i=0; i<tspans.length; i++)
            {
               tspan=tspans[i];
               if (!$(tspan).text().match(/^.(Entry|Do|Exit|eSM):/)) continue;
               words=$(tspan).text().split(" ");

               if (words.length>1)
               {
                  $(tspan).text(words.shift());
                  $(tspan).after($(tspan).clone().attr("dx",42).removeAttr("dy").text(words.join(" ")));
                  i++;
               } else ($(tspan).next().attr("dy",0));

               $(tspan).css("font-weight","bold");
            }

            $(tspans[0]).attr("dy",y);
         }

         updateStateBoxByText(state,margin,false);
      }

      if (state.underline)
      {
         state.underline.attr("path","M"+(state.attr("x")+(margin/2))+","+(state.attr("y")+33.5)+"l"+(state.attr("width")-margin)+",0");
         if (state.attr("height")>40) state.underline.show(); else state.underline.hide();
      }
   }

   // make sure that closest parents are updated
   // accordingly to the current state.
   function updateParents(states,parent)
   {
      var i,j;
      if (!states) states=g.states;
      if (!parent) parent=false;

      for (i=0; i<states.length; i++)
         states[i].parentState=parent;

      for (i=0; i<states.length; i++)
         for (j=0; j<states.length; j++)
            if (stateIsInside(states[j],states[i]))
               if (states[i].parentState==parent || stateIsInside(states[i].parentState,states[j]))
                  states[i].parentState=states[j];
   }

   // put given state to front
   // and bring to front all children too
   function putStateToFront(state)
   {
      state.toFront();
      if (state.text) state.text.toFront();
      if (state.underline) state.underline.toFront();
      if (g.connector) g.connector.toFront();
      if (g.enlarger) g.enlarger.toFront();
      putConnectionsToFront(state);

      var i;
      for (i=0; i<g.states.length; i++)
         if (g.states[i].parentState && g.states[i].parentState==state) putStateToFront(g.states[i]);
   }

   function putConnectionsToFront(state)
   {
      for (var i=0; i<g.connections.length; i++)
         if (g.connections[i].stateFrom.id==state.id || g.connections[i].stateTo.id==state.id)
         {
            g.connections[i].toFront();
            g.connections[i].arrowend.toFront();
            g.connections[i].text.toFront();
         }
   }


   function vertexHide(ev)
   {
      if (g.shifter)
      {
         if (!(ev && ev.target==g.shifter.node)) // do not hide if mouse clicks the shifter itself
            g.shifter.hide();
      }

      if (g.reattach2)
      {
         if (!(ev && ev.target==g.reattach2.node)) // do not hide if mouse clicks the reattacher itself
            g.reattach2.hide();
      }

      if (g.vertex)
      {
         if (!(ev && ev.target==g.vertex.node)) // do not hide if mouse clicks the vertex itself
            g.vertex.hide();
      }
   }

   function mouseOverArrow(ev,x,y)
   {
      var radius=5;
      if (mouseButtonIsDown()) return;

      var conn=paper.getElementByPoint(x,y);
      if (!conn) return;

      if (conn.parentPath) conn=conn.parentPath;

      // calculate length at our position
      var length=getLengthAtPoint(conn.coord, x+g.paperPanX, y+g.paperPanY);
      if (length<radius*3) length=radius*3;
      if (length>conn.getTotalLength()-radius*2) length=conn.getTotalLength()-radius*2;
      var p=conn.getPointAtLength(length);
      if (length<0) return;

      if (!g.vertex)
      {
         g.vertex=paper.rect(0,0, radius*2,radius*2,radius).attr("fill","red");
         g.vertex.mousemove(mouseOverArrow);
         g.vertex.drag(vertexdragmove,vertexdragstart,vertexdragend);
         g.vertex.dblclick(editboxOn);
      }
      g.vertex.toFront();
      g.vertex.parentPath=conn;
      g.vertex.attr({x: p.x-radius, y: p.y-radius}).show();

      // point at arrow start
      p=conn.getPointAtLength(radius/2);
      if (!g.shifter)
      {
         g.shifter=paper.rect(0,0, radius*2,radius*2, radius).attr({"fill":"#000",stroke:'#000'});
         g.shifter.mousemove(mouseOverArrow);
         g.shifter.drag(shifterdragmove,shifterdragstart,shifterdragend);
         g.shifter.dblclick(editboxOn);
      }

      if (!stateIsNote(conn.stateFrom) && !stateIsNote(conn.stateTo))
      {
         g.shifter.toFront();
         g.shifter.parentPath=conn;
         g.shifter.attr({x: p.x-radius, y: p.y-radius}).show();
      }
   }


   function changeStatePosition(state,x,dx,y,dy)
   {
      if (state.fwprop.type=='choice') state.attr("transform","");
      state.attr( {"x": x+dx, "y": y+dy} );
      if (state.fwprop.type=='choice') state.attr("transform","r45");
      updateStateText(state,true);

      // update vertexes position so they move together with states
      for(var j=0; j<state.vclone.length; j++)
         for(var k=0; k<state.vclone[j].length; k++)
         {
            state.vcopy[j][k].x=state.vclone[j][k].x+dx;
            state.vcopy[j][k].y=state.vclone[j][k].y+dy;
         }
   }


   function cloneVertexes(state)
   {
      state.vcopy=[];
      state.vclone=[];
      for (var i=0; i<g.connections.length; i++)
      if (g.connections[i].stateFrom.id==state.id)
      {
         state.vcopy.push(g.connections[i].vertexes);
         state.vclone.push(clone(g.connections[i].vertexes));
      }
   }


   function stateIsInside(parent,child)
   {
      if (parent.attr("x") < child.attr("x") &&
          parent.attr("y") < child.attr("y") &&
          parent.attr("x")+parent.attr("width") > child.attr("x")+child.attr("width") &&
          parent.attr("y")+parent.attr("height") > child.attr("y")+child.attr("height")
      ) return true;
      return false;
   }


   function clone(obj)
   {
       if (obj instanceof Array)
       {
           var copy = [];
           for (var i = 0, len = obj.length; i < len; i++) copy[i] = clone(obj[i]);
           return copy;
       }

       if (obj instanceof Object)
       {
           var copy = {};
           for (var attr in obj) if (obj.hasOwnProperty(attr)) copy[attr] = clone(obj[attr]);
           return copy;
       }

       return obj;
   }


   // set timeout with closure to preserve parameters
   function setTimeoutWithParams(fn,pAr,sec)
   {
      return setTimeout(function(){fn.apply(this,pAr);},sec);
   }
