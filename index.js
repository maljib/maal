// index.js

$(function() {
  "use strict";

  const MAX_MAIL_SIZE = 10 * 1024 * 1024;

  var uid = "", nick, name, mail, sure; // 사용자: 번호,아이디,이름,이메일,보증인
  var logged_in = false;
  var is_editor = false;
  var word, expl = [];        // 낱말, [0]=풀이 [1]=적바림
  var word0, data1;           // 지운 낱말, 편집 후 데이터
  var eEdit = {};
  var words = localStorage && localStorage.words && JSON?
                      JSON.parse(localStorage.words): [];
  var arg_words = [], arg_i = -1;
  showIf($("#download"), ("download" in document.createElement("a")));
  
  function info(s) {
    $("#msg").html(s).draggable({ cursor:"move" }).show()
             .click(function() { $(this).hide(); });
  }

  // 서버 에러 메시지를 출력한다 (서버 프로그램 이름, 메시지)
  function serverError(serverProgramName, message) {
    $("#tip").html("서버(" + serverProgramName + ") 에러: " + message).show();
  }

  // 입력 에러를 표시하고 에러 메시지를 출력한다 (객체, 메시지[, 출력 객체])
  function setError(o, message, oMessage) {
    o.addClass("ui-state-error");
    if (oMessage) {
      oMessage.html(message).show();
    } else {
      o.attr("title", message);
    }
  }

  // 에러 표시와 에러 메시지를 제거한다 (객체[, 출력 객체])
  function resetError(o, oMessage) {
    o.removeClass("ui-state-error");
    if (oMessage) {
      oMessage.hide();
    } else {
      o.removeAttr("title");
    }
  }

  // 메시지를 보여주거나 지운다 (객체, 정상인가?, 에러 메시지)
  function check(o, isOk, message) {
    if (isOk) {
      resetError(o);
    } else {
      setError(o, message);
    }
    return isOk;  // 정상일 때 true를 넘긴다
  }

  // 보이는 것의 메시지를 보여주거나 지운다 (객체, 정상인가?, 에러 메시지)
  function checkWhileVisible(o, isOk, message) {
    return o.is(":visible")? check(o, isOk, message): true;
  }

  function checkEmpty(field, display) {
    var o = $(field);
    return checkWhileVisible(o, o.val(), display +" 넣으시오.");
  }

  function checkName() { return checkEmpty("#name", "이름을"); }
  function checkSure() { return checkEmpty("#sure", "보증인 아이디를"); }

  function checkMail(o) {
    return checkWhileVisible(o,
      /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,})+$/.test(o.val()),
      "전자우편 주소 형식이 맞지 않습니다.");
  }

  function checkPhone() {
    var o = $("#phone");
    var v = o.val().trim();
    return checkWhileVisible(o, v == '' || /^\d+(-\d+)*$/.test(v),
                             "전화번호 형식이 맞지 않습니다.");
  }

  $("#name").change(function() { checkName(); });

  $("#mail,#a-mail").change(function() {
    var o = $(this);
    if (checkMail(o) && o.val() != mail) {
      $.post("checkMail.php", {mail: o.val()}, function(rc) {
        rc && setError(o, rc == '1'? "이미 가입한 전자우편 주소입니다.": rc);
      });
    }
  });

  $("body").keydown(function(e) {
    if (e.keyCode === $.ui.keyCode.ESCAPE) {
      $(".ui-autocomplete-input").autocomplete("close");
    }
  });

  $("#user,#q-req,#ask,#toSure,#list").keyup(function(e) {
    switch (e.keyCode) {
      case $.ui.keyCode.ESCAPE: $(this).find(".x-close").click(); return false;
      case $.ui.keyCode.ENTER:  $(this).find(".b-shade").click(); return false;
    }
  });

  $("#nick,#name,#mail,#sure").keydown(function(e) {
    if (e.keyCode === $.ui.keyCode.SPACE) return false;
  });

  $("#nick,#pass,#a-mail,#phone,#askt").val('');

  $("#nick").change(function() {
    var n = $(this).val();
    if (!logged_in) {
      resetError($("#pass,#name,#mail,#sure").val("")); // 그것 빼고 모두 지운다
      $("#tip").hide();
      uid = "";
      $.post("getUser.php", $(this), function(user) {
        uid  = user.id;
        nick = $("#nick").val();
        $("#name").val(name = user.name);
        $("#mail").val(mail = user.mail);
        $("#sure").val(sure = user.sure);
        if (uid) {
          if (uid == '0') uid = '';
          showIf($("#passx"), uid);
          $("#name_mail_sure,#quit").hide();
          $("#signin").show();            // 비밀번호 입력 줄
          $("#nick").val(nick = user.nick);
          $(nick? "#pass": "#nick").focus();
          $("#nick").prop("defaultValue", nick);
        } else {
          showUpdate();
        }
      }, "json").fail(function(xhr) {
        serverError("getUser.php", xhr.responseText);
      });
    } else if (n === nick) {
      resetError($(this));
      syncSureToNick(n);
    } else {
      $.post("getNickId.php", $(this), function(rc) {
        if (rc == '0') {
          syncSureToNick(n);
        } else if ($.isNumeric(rc)) {
          setError($("#nick"), "사용 중인 아이디입니다.");
        } else {
          serverError("getNickId.php", rc);
        }
      });
    }
  }).blur(function() {
    if (!$(this).val()) $(this).focus();
  });

  function showUpdate() {
    $("#nick").prop("defaultValue", '');
    syncSureToNick(nick);
    $("#signin").hide();            // 비밀번호 입력 줄
    $("#name_mail_sure").show();    // 사용자 등록 양식
    $("#name").focus();             // 이름 입력 줄로 이동한다
    showQuit();
  }

  function syncSureToNick(newNick) {
    var oldNick = $("#nick").prop("defaultValue");
    if (oldNick && oldNick === $("#sure").val()) {
      $("#sure").val(newNick);
    }
    $("#nick").prop("defaultValue", newNick);
  }

  $("#sure").change(function() {
    var n = $("#nick").val();  // 아이디
    var s = $("#sure").val();  // 보증인 아이디
    if (s && s === nick) {
      $("#sure").val(s = n);
    }
    if (s === n) {
      sureErrorIf($("#enter").is(":visible"));
    } else {
      $.post("getSureId.php", $("#sure"), function(rc) {
        if ($.isNumeric(rc)) {
          sureErrorIf(rc == '0');
        } else {
          serverError("getSureId.php", rc);
        }
      });      
    }
  });

  function sureErrorIf(b) {
    var o = $("#sure");
    check(o, !b, o.val() +" 님은 보증인이 될 수 없습니다.");
  }

  function checkPass() {
    if (uid) {
      var o = $("#pass"), pass = o.val().trim();
      o.val(pass);
      showIf($("#x-ask"), !pass);
      if (pass) {
        $.post("checkPass.php", o.serialize() +"&id="+ uid, function(rc) {
          switch (rc) {
          case '0': logged_in = true; showUpdate(); break;     // 맞음 - 정보 바꾸기
          case '1': logged_in = true; enter(); break;          // 맞음 - 들어가기
          case '2': setError(o, "비밀번호가 맞지 않습니다."); break; // 틀림
          case '3': $("#nick").change(); break;                // 없음
          default:  serverError("checkPass.php", rc);          // 에러
          }
        });
      } else {
        setError(o, "비밀번호를 넣으시오.");
      }
    }
  }

  $("#passx").click(function() {
    $(this).hide();
    var arg = $("#nick,#mail").serialize() +"&id=-"+ uid;
    $.post("confirmMail.php", arg, function(rc) {
      showMsg(rc, "confirmMail.php");
    });
  });

  // [확인] 버튼 처리
  $("#ok").click(function() {
    if ($("#signin").is(":visible")) {
      checkPass();    // 로그인
    } else if (checkName() && checkMail($("#mail")) && checkSure()) {
      $(this).hide();
      if (uid) {      // 사용자 정보 변경
        var arg = serialize("#nick", nick) + serialize("#name", name);
        var n = $("#nick").val(), s = $("#sure").val();
        if (n !== s || nick !== sure) {
          arg += serialize("#sure", sure);
        }
        if (arg) {    // 아이디, 이름, 보증인 변경 
          $.post("updateUser.php", "id="+ uid + arg, function(rc) {
            showMsg(rc, "updateUser.php");
            if (rc == '1' || rc == '2') {
              updateMail(rc);
            }
          });
        } else {
          updateMail();
        }
      } else {        // 새로 가입
        $.post("confirmMail.php", $("#nick,#name,#mail,#sure"), function(rc) {
          showMsg(rc, "confirmMail.php");
          if (rc == 'a') doCancel();
        });
      }
    }
  });

  function updateMail(rc0) {
    var arg = serialize("#mail", mail);
    if (arg) {    // 메일 주소 변경 있음
      arg = $("#nick").serialize() +"&id="+ uid + arg;
      $.post("confirmMail.php", arg, function(rc) {
        showMsg(rc, "confirmMail.php");
        rc == '0' && exitCloseDialog();
      });
    } else if (rc0 == '2') {
      exitCloseDialog();
    } else {
      rc0 == '1' && saveValues();
      closeDialog();
    }
  }

  function showMsg(rc, php, oMail) {
    if (rc === "a" || rc === "0") {
      info("전자우편을 열고 확인을 누르시오.");  // 확인 메일을 보냈다
    } else if (rc === '2') {   // 보증인이 변경되었다
      info($("#sure").val() +" 님이 보증하거나 거절하면 전자우편으로 알려드립니다.");
    } else if (rc !== '1') {   // 아이디 변경이 아니다 -- 에러
      $("#ok").show();
      if ($.isNumeric(rc)) {
        if (!oMail) oMail = $("#mail");
        check($("#nick"), (rc &  4) === 0, "이미 가입한 아이디입니다.");
        check(     oMail, (rc & 16) === 0, "이미 가입한 전자우편 주소입니다.");
        sureErrorIf(rc & 8);     // 보증인 아이디 에러
      } else {
        serverError(php, rc);  // 서버 에러
      }
    }
  }

  // [들어가기] 버튼 클릭  -- 로그인, 사용자 등록
  $("#enter").click(function() {
    $("#name_mail_sure,#quit,#x-ask,#passx").hide();
    $("#signin").show();  // 로그인 다이얼로그
    openDialog("들어가기");
  });

  // 사용자 버튼 클릭  -- 사용자 정보 변경
  $("#user_info").click(function() {
    $("#signin,#quit,#x-ask").hide();
    $("#name_mail_sure,#new_pass").show();
    openDialog(name);
    showQuit();
  });

  // [나가기] 버튼 클릭
  $("#exit").click(function() {
    $("#h1").text("배달말집");
    $("#t1,#t2,#t3,#exit_div,#arg,#arg-l,#arg-r").hide();
    $("#enter").show();            // [들어가기] 버튼을 보여준다
    eraseValues();                 // 입력 받은 값을 모두 지운다
    logged_in = false;
  });

  $("#quit").click(function() {
    $.post("deleteUser.php", "id="+ uid +",0,0", function(rc) {
      if (rc == '2') {
        doCancel();
        $("#q-sure").text(sure);
        $("#q-req").show();  
      } else if (rc == '1') {
        exitCloseDialog();
      } else {
        info("deleteUser.php: "+ rc);
      }
    });
  });

  $("#q-req .b-shade").click(function() {
    $.post("askQuit.php", $("#nick,#sure").serialize() +"&id="+ uid, function(rc) {
      if (!rc) {
        var sure1 = sure;
        $("#exit").click();
        $("#q-req").hide();
        info(sure1 +" 님이 탈퇴를 승낙하거나 거절하면 전자우편으로 알려드립니다.");
      } else {
        info("askQuit.php: "+ rc);
      }
    });
  });

  $("#q-req .x-close").click(function() { $("#q-req").hide(); });
  $(  "#ask .x-close").click(function() {   $("#ask").hide(); });

  $("#x-ask").click(function() {
    $(this).hide(); 
    $("#ask").show();
  });

  $("#phone").change(function() { checkPhone(); });

  $("#ask .b-shade").click(function() {
    var oMail = $("#a-mail");
    if (checkMail(oMail) && checkPhone()) {
      if (oMail.val() === mail) {
        $("#ask").hide();
        $("#passx").click();
      } else {
        var o = $(this).hide();
        var arg = $("#nick,#mail,#a-mail,#phone,#askt").serialize();
        $.post("confirmMail.php", arg +"&id=@"+ uid, function(rc) {
          showMsg(rc, "confirmMail.php", oMail);
          if (rc === "0") {
            $("#ask").hide();
            exitCloseDialog();
          }
          o.show();
        });
      }
    }
  });

  function showQuit() {
    if (uid) {
      $.post("showQuit.php", "id="+ uid, function(rc) {
        if (rc == '0' || rc == '1') {
          showIf($("#quit"), rc == '1');
        } else {
          serverError("showQuit.php", rc);
        }
      });
    } else {
      $("#quit").hide();
    }
  }

  // 입력한 값을 저장한다
  function saveValues() {
    nick = $("#nick").val();       // 아이디
    mail = $("#mail").val();       // 전자우편 주소
    name = $("#name").val();       // 이름
    sure = $("#sure").val();
  }

  // 입력한 값을 모두 지운다
  function eraseValues() {
    $("#user input").val("");
    saveValues();
    uid = "";
    expl = [];
    $("#arg").val(word = "");
    $("#edit").val(data1 = eEdit.data = "");
    $("#tab1,#tab2,#tab3,#viewer").empty();
    $("#list,#note-form,#ans,#toSure,#word,#editor,#count1,#count2,#count3,#count4,#count5,#editors").hide();
    $("#t1,#t2,#t3").removeAttr("title");
    $("#t1").click();
  }

  function h1() {
    $("#h1").text($(window).width() > 400? "배달말집":"말집");
  }

  // 정상 로그인 후 입력 받은 값을 저장하고 팝업 창을 닫는다
  function enter() {
    saveValues();             // 입력 받은 값을 저장한다
    h1();
    $("#enter,#msg").hide();  // [들어가기] 버튼을 숨기고
    $("#exit_div").show();    // [(이름)]과 [나가기] 버튼을 보여준다
    $("#user_info").attr("title", nick);
    $("#arg").show();         // 올림말 입력 창을 보여준다
    arrows();
    closeDialog();            // 팝업 창을 닫는다
    showCount([1, 2, 3, 4, 5]);

    $.post("askData.php", {id: uid}, function(array) {
      if (0 < array.length) {
        var tbody = $("#ans tbody").empty();
        array.forEach(function(a) {
          tbody.append("<tr title='"+ a[6] +"'>"+
                "<td><input type='radio' name='a"+ a[0] +"'>o "+
                    "<input type='radio' name='a"+ a[0] +"'>x "+
            "<input checked type='radio' name='a"+ a[0] +"'>?<br>"+
                                        "<small>"+ a[3] +"</small></td>"+
                      "<td>"+ a[1] +"<br>"+ (a[4]? a[4]: "&nbsp;") +"</td>"+
                      "<td>"+ a[2] +"<br>"+        a[5] +"</td></tr>");
        }); // askId_userId, 등록(아이디(이름), 메일), 입력(time, 전화, 메일, 알림글)
        $("#ans").show();
      }
    }, 'json').fail(function(xhr) {
      serverError("askData.php", xhr.responseText);
    });

    $.post("toSure.php", {id: uid}, function(array) {
      var count = array.length;
      if (0 < count) {
        var o = $("#toSure tbody").empty();
        for (var i = 0; i < count; i++) {
          var a = array[i];
          o.append("<tr><td>"+ (a[3] == '0'? "보증": "탈퇴") +": "+
                   "<input type='radio' name='s"+ a[0] +"'>o "+
                   "<input type='radio' name='s"+ a[0] +"'>x "+
                   "<input type='radio' name='s"+ a[0] +"' checked>?</td><td>"+
                               a[1] +"</td><td>"+ a[2] +"</td><td>"+
                   "<img src='glyphicons-11-envelope.png'></td></tr>");
        }
        $("#toSure").show();
      }
    }, 'json').fail(function(xhr) {
      serverError("toSure.php", xhr.responseText);
    });

    $.post("isEditor.php", {id: uid}, function(rc) {
      is_editor = rc == '1';
    });
  }

  function showCount(nArray) {
    nArray.forEach(function(n) {
      $.post("getCount"+ n +".php", function(count) {
        $("#count"+ n).text(count).show();
      });
    });
  }

  // 데이터가 변경되었으면 파라미터로 만든다 (저장된 값, 데이터 선택자)
  function serialize(field, saved) {
    var o = $(field);
    return o.is(":visible") && o.val() != saved? "&"+ o.serialize(): "";
  }

  // 제목 줄에 제목을 넣고 팝업 창을 띄운다 (제목)
  function openDialog(title) {
    $("#title").text(title);
    $("#user").css("top", "0").show();
    $("#nick").focus();
    $("#ok").show();
    $("#nick,#name,#sure").change(function() {
      $(this).val($(this).val().replace(/[ <>'"%&;\\]/g, ""));
    });  // 입력 칸에 특수 글자가 들어가지 않게 막는다
  }

  function closeDialog() {
    $("#user").hide();
    $("#pass").val("");                // 비밀번호를 지운다
    $("#tip").hide();                  // 서버 에러 표시를 제거한다
    resetError($("#user input"));      // 에러 표시를 모두 제거한다
    if ($("#enter").is(":visible")) {
      logged_in = false;
    }
  }

  function exitCloseDialog() {
    $("#exit").click();
    closeDialog();
  }

  $("#user .x-close").click(doCancel);

  $("#ans .b-shade").click(function() {
    $("#ans tbody>tr").each(function(row) {
      var o = $(this).find(":checked");
      var index = o.index();
      if (index < 2) {
        var arg = index +"_"+ o.attr("name").substring(1);
        $.post("resolveAsk.php", {a: arg}, function(rc) {
          if (rc) info((row + 1) +"번째 줄 - resolveAsk.php: "+ rc);
        });
      }
    });
    $("#ans").hide();
  });

  $("#toSure .b-shade").click(function() {
    $("#toSure tbody>tr").each(function(i) {
      var o = $(this).find(":checked");
      var index = o.index();
      if (index < 2) {
        var  op = o.parent().text().substring(0,2) == "보증"? 0: 1;
        var php = op + index === 0? "upRank.php": "deleteUser.php";
        var arg = "id="+ o.attr("name").substring(1);
        if (op === 1) arg += ","+ (index === 0? uid: "0");
        $.post(php, arg +"&nick="+ encodeURIComponent(nick), function(rc) {
          if (rc != '1') info("에러["+ (i + 1) +"] "+ rc);
        });
      }
    });
    $("#toSure").hide();
  });

  $("#toSure>tbody").on("click", "img", function() {
    var tr = $(this).parent().parent();
    var id = tr.find(":checked").attr("name").substr(1);
    $("#toSure .x-close").hide();
    $("#send-note").show();
    $("#note-form").width($("#toSure").width()).show()
                   .position({ my:"left top", at:"left-1 bottom", of:tr })
                   .data([function() { return id; }, "#toSure"]);
    setNoteSize();
    return false;
  });

  $("#download").click(function() {
    var o = $(this);
    o.hide();
    $.post("download.php", function(name) {
      var link = document.createElement("a");
      link.download = name;
      link.href = "p/maljib.pdf";
      link.click();
      o.show();
    });
  });

  // [취소] 버튼 처리
  function doCancel() {
    if ($("#enter").is(":visible")) {  // 사용자 등록/로그인 중이었으면
      eraseValues();                      // 입력한 것을 모두 지운다
    } else {                           // 사용자 정보 수정 중이었으면
      $("#nick").val(nick);               // 아이디(nick)와
      $("#name").val(name);               // 이름을 되돌린다
      $("#mail").val(mail);               // 전자우편 주소와
      $("#sure").val(sure);               // 전자우편 주소와
    }
    closeDialog();                     // 팝업 창을 닫는다
  }

  var JAMO = ["ㄱ","ㄴ","ㄷ","ㄹ","ㅁ","ㅂ","ㅅ","ㅇ","ㅈ","ㅊ","ㅋ","ㅌ","ㅍ","ㅎ",
              "가","나","다","라","마","바","사","아","자","차","카","타","파","하"];

  function circled(c) {
    var n = c.charCodeAt(0);
    if (0x41 <= n && n <= 0x5a)     return String.fromCharCode(n + 0x2475);
    if (0x61 <= n && n <= 0x7a)     return String.fromCharCode(n + 0x246f);
    if (0 <= (n = JAMO.indexOf(c))) return String.fromCharCode(n + 0x3260);
    return "";
  }

  function circledNumber(n) {
    n += n < 1? 0x24ea: n < 21? 0x245f: n < 36? 0x323c: 0x328d;
    return String.fromCharCode(n);
  }

  var ALNUM = /[-0-9A-Za-z\u3130-\u318f가-힣]/;
  function bounded(s, i) {
    return i === 0 || !ALNUM.test(s[i - 1]);
  }

  var CIRCLED = /[】:〕①-⑳㉑-㉟㊱-㊿]/g;
  function num(s) {
    var n = 0, maal = false;
    return s.replace(CIRCLED, function(u,i,s) {
      switch (u) {
      case "〕": n = 0; maal = false; break;
      case "】": n = 0; maal = true;  break;
      case ":":  if (maal) n = 0;   break;
      default:   if (bounded(s, i)) return circledNumber(n = n % 50 + 1);
      }
      return u;
    });
  }

  function convert(s) {
    var w = $("#t1").text().replace(/(.+?)\d*$/, "$1");
    s = s.replace(new RegExp("^\\s*("+ w +")?\\d*\\s*"), "")

         .replace(/이음꼴\/맞이\)/g, "이음꼴/맞섬)")
         .replace(/\(딸이\)/g, "(딸림)")
         .replace(/\(도이\)/g, "(도움)")
         .replace(/\(바꿈꼴\/이바\)/g, "(바꿈꼴/이름)")
         .replace(/\(매바\)/g, "(매김)")
         .replace(/\(마침꼴\/여마\)/g, "(마침꼴/여느)")
         .replace(/\(느마\)/g, "(느낌)")
         .replace(/\(물마\)/g, "(물음)")
         .replace(/[≪《](.+?)[》≫]/g, "《$1》")

         .replace(/[「\[](명사?|이(름씨)?)[\]」]/g, "〔이〕")
         .replace(/[「\[](대명사?|대이(름씨)?|대)[\]」]/g, "〔대이〕")
         .replace(/[「\[](의존명사|매인이름씨|매이)[\]」]/g, "〔매이〕")
         .replace(/[「\[](수사?|셈씨?)[\]」]/g, "〔셈〕")
         .replace(/[「\[](동사?|움(직씨)?)[\]」]/g, "〔움〕")
         .replace(/[「\[](자동사?|제움(직씨)?)[\]」]/g, "〔제움〕")
         .replace(/[「\[](타동사?|남움(직씨)?)[\]」]/g, "〔남움〕")
         .replace(/[「\[](조동사?|도움(움직씨)?)[\]」]/g, "〔도움〕")
         .replace(/[「\[](형용사?|그(림씨)?)[\]」]/g, "〔그〕")
         .replace(/[「\[](보조형용사|도움그림씨|도그)[\]」]/g, "〔도그〕")
         .replace(/[「\[](관형사?|매김씨?)[\]」]/g, "〔매김〕")
         .replace(/[「\[](부사?|어찌씨?|어)[\]」]/g, "〔어찌〕")
         .replace(/[「\[](감탄사?|느낌씨?)[\]」]/g, "〔느낌〕")
         .replace(/[「\[](조사?|토씨?)[\]」]/g, "〔토〕")
         .replace(/[「\[](어미|씨끝)[\]」]/g, "〔씨끝〕")
         .replace(/[「\[](접사|가지)[\]」]/g, "〔가지〕")
         .replace(/[「\[](접두사?|앞(가지)?)[\]」]/g, "〔앞〕")
         .replace(/[「\[](접요사?|속(가지)?)[\]」]/g, "〔속〕")
         .replace(/[「\[](접미사?|뒷(가지)?)[\]」]/g, "〔뒷〕")
         .replace(/「(\d+)」|(^|[^-A-Za-z\u3130-\u318f가-힣])(\d+)\./g,
                 function(u,u1,u2,u3) {
                   return u1? circledNumber(parseInt(u1)):
                         u2 + circledNumber(parseInt(u3));
                 })
         .replace(/[【\[](쓰임|보기)[】\]]|\u2225/g, "¶")
         .replace(/\[(본(딧말)?|본디)\]/g, "<")
         .replace(/\[준(말)?\]/g, ">")
         .replace(/\[(동의어|한뜻말?|한)\]/g, "=")
         .replace(/\[(유의어|비슷(한말)?|비)\]|≒/g, "≈")
         .replace(/\[(맞선말?|맞)\]/g, "↔")
         .replace(/⇒/g, "→")
         .replace(/\[큰말\]/g, "[큰]")
         .replace(/\[작(은말)?\]/g, "[작은]")
         .replace(/\[센말\]/g, "[센]")
         .replace(/\[여(린말)?\]/g, "[여린]")
         .replace(/\[높(임말)?\]/g, "[높임]")
         .replace(/\[낮(춤말)?\]/g, "[낮춤]")
         .replace(/\[갈(래말)?\]/g, "[갈래]")
         .replace(/\[(끝바꿈|덧풀이|익은말|옛말)\]/g, "【$1】")
         .replace(/\s*(\[덧붙임\]|\*\*\*덧풀이\*\*\*)\s*/, "\n【덧풀이】")
         .replace(/말】(\s*[^【]+)+/g, function(u) {
           return u.replace(/;/g, ":");
         }).replace(/[⁰¹²³⁴-⁹]/g, function(u) {
           var c = u.charCodeAt(0);
           return c == 0xb9?
                  "1": String.fromCharCode(c - (c < 0x2070? 0x80: 0x2040));
         }); //.replace(new RegExp(w, "g"), "~");
    return num(s);     
  }

  var  LINK = /([<>=≈↔→\]☛])\s*([-가-힣]+\d*)((\s*[,.]\s*[-가-힣]+\d*)*)/g;
  var START = /([〕①-⑳㉑-㉟㊱-㊿])\s*(\(.+?\))\s*/g;
  
  function html(s) {
    return "<span class='maal-word'>"+ word.replace(/0*(\d+)$/, "<sup>$1</sup>")
          +"</span><span class='maal-text'>"+
    s.replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(LINK, function(s, s1, s2, s3) {
      var t = s1 +" <span data-l='"+ s2 +"'>"+ s2 +"</span>";
      if (s3) {
        s3.split(/\s*[,.]\s*/).forEach(function(x) {
          x = x.trim();
          if (x) {
            t += ", <span data-l='"+ x +"'>"+ x +"</span>";              
          }
        });
      }
      return t;
    })
    .replace(/([-가-힣]+)0*(\d+)(.?)/g, function(s, s1, s2, s3) {
      return s3 === "'"? s: s1 +"<sup>"+ s2 +"</sup>"+ s3;
    })
    .replace(/꿈】\s*[^【〔]+/, function(u) {
      return u.replace(/\s*(\(.+?\))\s*/g, " <i>$1</i> ")
              .replace(/(】)\s*/, "$1");
    })
    .replace(START, "$1<i>$2</i> ")
    .replace(/말】(\s*[^【]+)+/g, function(u) {
      return u.replace(/([】.])\s*(([^.:]|\(.*?\))+)[:]\s*/g,
                                              "$1<br><b>$2</b>: ");
    })
    .replace(/^\s*([^〔【])/, "&nbsp;$1")
    .replace(/(〔|【)/g, "<br>$1")
    .replace(/\s*(¶|☛)\s*/g, " $1")
    .replace(/{(.+?)}/g, "<b>$1</b>")
    .replace(/〔(.+?)〕/g, "<span class='maal-ps'>$1</span>") + "</span>";  // 씨가름
  }

  function convertText(s) {
    return s? s.replace(/ /g, "&nbsp;")
               .replace(/\r\n/g, "\n")
               .replace(/\n/g, "<br>")
               .replace(/［(.+?)］/g, "<span class='maal-box'>$1</span>")
               .replace(/#\((.+?)(\|(.+?))?\)/g, function(s,s1,s2,s3) {
                 var a;
                 if (s1[0] === "#") {
                   var c = s1.substring(1);
                   a = s1 + (s3? "' id='"+ c +"_": "_' id='"+ c);
                   s1 = "&#x21E7;";
                 } else {
                   a = s1 +"' target='_blank";
                 }
                 return "<a href='"+ a +"'>"+ (s3? s3: s1) +"</a>";
               }): "";
  }

  function diff(a, b) {
    if (!a) a = " ";
    if (!b) b = " ";
    return $("<div>").append("<div class='diff'>").prettyTextDiff({
      cleanup: true, originalContent: a, changedContent: b
    }).children().html();
  }

  function tellName(t) {
    return "<i>"+ ["더살핌","올림","버림"][t] +"&nbsp;</i>";
  }

  function accordion(o, data, collapse, n, isForum) {
    o.empty().accordion({ animate: false, icons:false,
                      collapsible: collapse,
                      heightStyle: 'content' });
    o.append(data).accordion("refresh");
    if (isForum) {
      o.accordion("option", "active", false);      
    } else {
      o.accordion("option", "active", n);
      o.find(".a-cmd:eq("+ n +")").show();
      o.find(".a-head:eq("+ n +")").hide();  
    }
  }

  function push(array, element) {
    var i = array.indexOf(element);
    if (0 <= i) array.splice(i, 1);
    array.unshift(element);
    return array;
  }
    
  function arrows() {
    showIf($("#arg-l"), arg_i < arg_words.length - 1);
    showIf($("#arg-r"), arg_i > 0);
  }

  $("#arg-l,#arg-r").click(function() {
      if (arg_words.length) {
        arg_i += $(this).attr("id") == "arg-l"? 1: -1;
        $("#arg").val(arg_words[arg_i]).autocomplete("search");          
        arrows();
      }
  });

  $("#arg").autocomplete({
    delay: 800,
    source: function(request, response) {
      var s = request.term;
      if (s == "!") {
        response(words);
      } else if (s == "!!") {
        $.post("recent.php", function(data) {
          var j = 1;
          for (var i = 1, len = data.length; i < len; i++) {
            if (j <= data.indexOf(data[i])) data[j++] = data[i]; 
          }
          data.length = j;
          response(data);
        }, "json");
      } else {
        var arg = "", a = s.match(/^\s*(.*?)([@#$^&*])(.*?)\s*$/);
        if (a) {
          if (a[2] != "*") { // @|#|$|^|&   -- nick : id
            arg = a[3]? "n="+ encodeURIComponent(a[3]): "i="+ uid;
            switch (a[2]) {
            case "#": arg = "e"+ arg; break; // expls
            case "$": arg = "m"+ arg; break; // memos
            case "^": arg = "1"+ arg; break; // 올림
            case "&": arg = "2"+ arg; break; // 버림
            }
          }
          if (a[1]) {
            if (arg) arg += "&";
            arg += "a="+ encodeURIComponent(a[1]);  // after
          }
        } else {
          arg = "l="+ encodeURIComponent(s.trim());  // like
        }
        $.post("search.php", arg, function(data) {
          response(data);
        }, "json");
      }
    },
    select: function(e, ui) {
      push(arg_words, $("#arg").val());
      if (uid) findWord0(ui.item.value);
      arg_i = -1;
      arrows();
    }
  }).keydown(function(e) {
    if (e.keyCode === $.ui.keyCode.ENTER) {
      var arg = $(this).val().trim().replace(/\s+(0*\d+)$/, "$1");
      $(this).val(arg);
      if (arg) {
        if (/[!@#$^&*]/.test(arg)) {
          $("#arg").autocomplete("search");
        } else {
          findWord0(arg);
        }
      }
    }
  });

  function findWord0(arg) {
    $("#arg").blur();
    $("#editors").hide();
    findWord(arg, true);
  }

  function findWord(arg, isNew) {
    if (!arg) arg = word;
    $("#t1,#t2,#t3").hide();
    if (!arg) return;

    $.post("getWord.php", "arg=" + encodeURIComponent(arg), function(w) {
      $("#t1").text(word = arg).show();
      $("#t3").text(word === "?"? "알림판": "적바림");
      if (w.wid) {
        w.data = w.data.replace(/\r\n/g, "\n");
        expl   = [[w]];
        var sTell = word == "?"? "": tellName(w.tell);
        var  data = (sTell? html: convertText)(w.data);
        accordion($("#tab1"), fill(w.t, w.nick, sTell, data), false, 0);
        fills($("#tab2"), 0);  // 자취
        fills($("#tab3"), 1);  // 적바림
        $("#t1").css("cursor", w.uid == uid? "pointer": "default");
        if (isNew) {
          $(word === "?"? "#t3": "#t1").click();
        }
        if (word != "?" && word != words[0]) {
          push(words, word);
          if (JSON) localStorage.words = JSON.stringify(words);
        }
      } else {
        expl = [];
        $("#t3").show();
        $("#tab1,#tab2,#tab3").empty();
        edit(0,-1);
      }

      function fill(t, sNick, sTell, data, i, h) {
        var owner  = w.uid == uid;
        var editor = owner || is_editor && w.tell == '1';
        if (sTell) {
          if (owner)  sNick = "<span id='w-nick'>"+ sNick +"</span>";
          if (editor) sTell = "<span id='w-tell'>"+ sTell +"</span>";
        }
        var bar = "<div><span>"+ sNick +" <small>"+ t +"</small> "+ sTell +"</span>"+
                  (h? h: "") +"<span class='a-cmd'>";
        if (i === 0) {
          if (editor) {
            bar += "<span class='a-accept b-yellow'>&nbsp;올림&nbsp;</span>";
          }
          bar += "<span class='a-view b-green'>&nbsp;보기&nbsp;</span>";
        }
        return bar +"<span class='a-edit b-"+
               (i === 1? "white": (editor? "yellow": "green")) +
               "'>&nbsp;손질&nbsp;</span> &nbsp;</span></div><div><div class='a-data'>"+
               data +"</div></div>";
      }

      function bb(s) {
        return s.replace('<', '⧼').replace('>', '⧽');
      }

      function fills(o, i) {
        o.empty();
        var w_id = i === 0? ","+ w.id: "";
        $.post("getData.php", "arg="+ w.wid + w_id, function(array) {
          var tx = $(i? "#t3": "#t2");
          var len = array.length;
          if (len) {
            var j = -1, isEdit = eEdit.i === i && eEdit.data;
            var data = "";
            for (var index = 0; index < len; index++) {
              var a = array[index];
              if (j < 0 && isEdit && eEdit.data == a.data) j = index;
              a.data = a.data.replace(/\r\n/g, "\n");
              var b = bb(a.data);
              var h = word == "?" && i == 0? "":
                "<span class='a-head'>"+ b.trim().split('\n', 1)[0] +"</span>";
              data += fill(a.t, a.nick, "",
                           i? convertText(a.data): diff(bb(w.data), b), i, h);
            }
            var isForum = i && word === "?";
            accordion(o, data, true, j < 0? 0: j, isForum);
            if (!isForum) {
              tx.html("<div style='margin:-1.5px 0'>"+ (i? "적바림": "자취") +
                      "<sup>"+ len +"</sup></div>");
            }
            if (i) {
              o.append("<br><br>");  // 이곳을 클릭하면 새 적바림 쓰기 창이 열린다
            } else {
              if (0 <= j) {
                j++;
              } else if (eEdit.i === 0 && eEdit.data === w.data) {
                j = 0;
              }
              array.unshift(w);
            }
            expl[i] = array;
            if (0 <= j) {
              eEdit = array[j];
              setEditTitle(i, j);
            }
            fitHeaders(o);
            tx.show();
          } else if (i) {
            tx.show();
          }
        }, "json");
      }
    }, "json");
  }

  function fitHeaders(o) {
    var hidden = !(o.is(":visible"));
    if (hidden) o.show();
    var headers = o.find(".ui-accordion-header");
    var w = headers.width() - 5;
    for (var i = 0, len = headers.length; i < len; i++) {
      var h = $(headers[i]).children();
      $(h[1]).width(w - $(h[0]).width());
    }
    if (hidden) o.hide();
  }

  function toNum(s, i) {
    if (0 <= i) {
      var n = s.charCodeAt(i);
      if (48 <= n && n <= 57) return n - 48;
    }
    return -1;
  }

  function isWord() { return eEdit.i === 0 && word !== "?"; }

  function insert(o) {
    var d = o.attr("data-a");
    if (d) {
      var t = $("#edit").focus();
      var a = t.prop("selectionStart");
      var b = t.prop("selectionEnd");
      var v = t.val();
      var i = a + d.length;
      if (d[0] === "【") {
        d = "\n" + d;
        i++;
      } else if (10 < a && v[a - 1] === "/" && d[0] === "(") {
        d = d.substr(1);
        i--;
      } else if (d === "‘’" || d === "“”" || d === "《》" || d === "〈〉" || d === "［］") {
        i--;
      } else if (d === "◯") {
        var j = a - 1, n10, n = toNum(v, j);
        if (0 <= n && 0 <= (n10 = toNum(v, j - 1))) {
          n += n10 * 10;
          j--;
        }
        if (1 <= n && n <= 50) {
          d = circledNumber(n);
          a = j;
          i = j + 1;
        } else if ((n = circled(v[j]))) {
          d = n;
          i = a--;
        } else if (isWord() && bounded(v, a)) {
          d = "①";
        }
      }
      v = v.substr(0, a) + d + v.substr(b);
      if (isWord()) v = num(v);
      t.val(v).prop("selectionStart", i).prop("selectionEnd", i);
    }
    return d;
  }

  function updateEdit() {
    eEdit.data = data1;
    isSame();
    findWord();
  }

  function viewIt(t, nick, data, toTab) {
    accordion($("#viewer"), "<div> "+ nick +" <small>"+ t +
      "</small><span class='a-cmd'>&#10006; &nbsp; </span></div>"+
      "<div><div class='a-data'>"+ (word == "?"? convertText: html)(data) +
      "</div></div>", false, 0);
    $("#t6").click();
    $("#viewer").data([toTab]);
  }

  function viewData() {
    var t = eEdit.j < 0? "새 올림말 풀이": eEdit.t;
    var n = eEdit.j < 0? nick: eEdit.nick;
    viewIt(t, n, $("#edit").val(), "#t5");
  }

  function revertData() {
    if (word === "") {
      word = word0;
      eEdit.j = -1;
    }
    $("#edit").val(isSame()? data1: eEdit.data);
    isSame();
  }

  function saveData() {
    if (isWord()) {
      $("#edit").val(data1 = data1.trim());
    }
    var arg, argData = "&"+ $("#edit").serialize();
    var i = eEdit.i, j;
    if (i === 0 && eEdit.j < 0) {
      $("#arg").val(word);  // 새 낱말
      arg = "&"+ $("#arg").serialize() + argData;
      $.post("addWord.php", "uid="+ uid + arg, function(rc) {
        if (rc == '1') {
          eEdit.j = 0;
          updateEdit();
          showCount([1]);
        } else {
          info("addWord.php: "+ rc);
        }
      });
    } else {
      var isWordAuthor = isWord() && expl[0][0].uid == uid;
      var len = expl[i]? expl[i].length: 0;
      for (j = len; 0 < j--;) {
        if (j !== eEdit.j && expl[i][j].data === data1) { // 같은 풀이가 있으면
          if (j && (isWordAuthor || i === 0 && word === "?")) {
            accept(j, false);
          }
          if (0 <= eEdit.j && eEdit.uid == uid && !isWordAuthor) {
            deleteData(false);
          }
          updateEdit();
          return;
        }
      }
      if (i === 0) { // 풀이: 살피는이가 손본 것은 무조건 추가 
        if (word !== "?" && expl[0][0].uid == uid) {
          j = -1;
        } else {  // 풀이: 자기 자취가 있으면 그곳에 업데이트, 없으면 추가
          for (j = len; 0 < j-- && expl[0][j].uid != uid;);
        }
      } else {  // 적바림: 손본 적바림이 자기 것이면 업데이트, 남의 것이면 추가
        j = "j" in eEdit? eEdit.j: -1;
        if (0 <= j && expl[1][j].uid != uid) j = -1;
      }
      arg = "a="+ i +",";
      if (j < 0) {
        arg += expl[0][0].wid +","+ uid + argData;
        $.post("addData.php", arg, function(rc) {
          if (rc == '1') {
            updateEdit();
            showCount([1, 2, 3]);
          } else {
            info("addData.php: "+ rc);
          }
        });
      } else {
        arg = "i="+ i +"&id="+ expl[i][j].id + argData;  // 그곳에 업데이트한다
        $.post("updateData.php", arg, function(count) {
          if (count == '1') {
            updateEdit();
          } else {
            info("updateData.php: "+ count);
          }
        });
      }
    }
  }

  function deleteData(isDelete) {
    var arg = "i="+ eEdit.i +"&a="+ eEdit.id;         
    var deleteWord = isDelete && eEdit.i === 0 && eEdit.j === 0;
    if (deleteWord) arg += ","+ eEdit.wid;
    $.post("deleteData.php", arg, function(rc) {
      if (rc == "1") {
        if (isDelete) {
          var i = eEdit.i;
          eEdit = { i:i, data:"" };
          $("#edit").val("");
          if (deleteWord) {
            word0 = word; word = "";
          }
          findWord(word);
        }
        showCount([1, 2, 3, 5]);
      } else {
        info("deleteData.php: "+ rc);
      }
    });
  }

  function convertData() {
    var o = $("#edit");
    o.val(convert(o.val()));
    isSame();
  }
  
  function attachMenu(menu, to, lr) {
    $(menu).menu({
      select: function(e, ui) {
        var item = ui.item;
        if (!item.is(":has(ul)")) {  // leaf
          $(menu).hide();
          if (!insert(item)) {
            switch (item.attr("id")) {
            case    "view":
            case   "view1":    viewData();     break;
            case  "revert":  revertData();     break;
            case    "save":    saveData();     break;
            case  "delete":  deleteData(true); break;
            case "convert": convertData();     break;
            }
          }
  	    }
      }
    }).position({my:lr+" top-"+ $(to).offset().top, at:lr+" bottom-3", of:to});

    $(menu +","+ to).hover(function() {
      $(menu).show();
    }, function() {
      $(menu).hide();
    });
  }

  $("#menu span").hover(function() {
    this.classList.add("ui-state-focus");
  }, function() {
    this.classList.remove("ui-state-focus");
  });

  attachMenu("#m0", "#s0", "left");
  attachMenu("#m2", "#s2", "left");
  attachMenu("#m3", "#s3", "right");
  attachMenu("#m4", "#s4", "left");
  attachMenu("#m5", "#s5", "right");
  attachMenu("#m6", "#s6", "right");
  $("#menu>span[data-a]").click(function() { insert($(this)); });

  $("#tabs").tabs();
  
  function showIf(o, condition) {
    if (condition) {
      o.show();
    } else {
      o.hide();
    }
  }

  function isDeletable() {
    return eEdit.uid == uid &&
          (eEdit.i || eEdit.j || expl[0][0].tell == 2 && expl[0].length === 1 &&
                                (expl.length === 1 || expl[1].length === 0));
  }

  $("#s0").mouseover(function() {
    showIf($("#view"),   isSame() && eEdit.data && eEdit.i === 0 && word != "?");
    showIf($("#view1"), !isSame() &&      data1 && eEdit.i === 0 && word != "?");
    showIf($("#revert"), eEdit.data != data1);
    showIf($("#save"),   word && !isSame() && data1.trim());
    showIf($("#delete"), isDeletable());
    showIf($("#convert"), eEdit.i === 0 && word != "?" && $("#edit").val().trim());
  });

  $("#s6").mouseover(function() { showIf($("#box"), word === "?"); });

  function tx() { return eEdit.i? "#t3": (0 < eEdit.j? "#t2": "#t1"); }

  function setEditTitle(i, j) {
    eEdit.i = i;
    eEdit.j = j;
    $("#t1,#t2,#t3").removeAttr("title");
    // $(tx()).attr("title", eEdit.nick +" "+ eEdit.t);
  }

  function edit(i, j) {
    $("#s0").css("background-color", ["#ffa","#bfc","white"][i]);
    if (i && --i === 0) j++;
    $("#edit").attr("placeholder",
      i? (word == "?"? "\n 제목\n\n알림글": "'"+ word +"' 적바림"):
      "[소리] (한자/밑말)\n"+
      "〔씨갈래〕(말본)①풀이. ¶쓰임. [이웃]... ... \u2461 . . .\n"+
      "〔씨갈래〕①(말본)풀이. ¶쓰임. [이웃]... ... \u2461(말본) . . ."+
      "\n   .  .  .\n"+
      "【덧풀이】\n   .  .  .\n【익은말】\n익은말: 풀이.\n"+
      "【옛말】\n옛말: 풀이.\n옛말:①풀이. \u2461풀이.\n"+
      "【끝바꿈】(이음꼴/맞섬)... ... (딸림)... ... (도움)... ...\n   .  .  .\n");
    if (j < 0) {
      eEdit = { data:"", nick:nick, t:"" };
    } else {
      eEdit = expl[i][j];
    }
    setEditTitle(i, j);
    $("#t5").click();
    $("#edit").val(data1 = eEdit.data).focus().scrollTop(0);
  }

  function isSame() {
    var o = $("#edit"), v = o.val(), v1;
    if (isWord() && v !== (v1 = num(v))) {
      var i = o.prop("selectionStart"), j = o.prop("selectionEnd");
      o.val(v = v1).prop("selectionStart", i).prop("selectionEnd", j);
    }
    var same = v === eEdit.data;
    if (!same) data1 = v;
    $("#tabs").tabs("option", "disabled", same? []: [0,1,2,3]);
    showIf($("#count1,#count2,#count3,#count4,#count5,#arg,#exit_div"), same);
    return same;
  }

  $("#edit").blur     (function() { isSame(); });
  $("#menu").mouseover(function() { isSame(); });

  $("#edit").keydown(function(event) {
    if (event.ctrlKey || event.metaKey) {
      var key = String.fromCharCode(event.which).toLowerCase();
      if (key == 's' || key == 'z' || key == 'q') { // || key == 'w') {
        event.preventDefault();
        switch (key) {
        case 's': if (!isSame() && data1.trim()) saveData(); break;
        case 'z': revertData(); break;
        case 'q': convertData(); break;
//        case 'w': viewData(); break;
        }
        return false;
      }
    }
  });

  $("#t1,#t2,#t3").click(function() {
    $(this).removeAttr("title");
  });
 
  $("#t1").dblclick(function() {
    var oldWord = $(this).text();
    if (oldWord === "?") {
      $("#t3").click();    
    } else if (expl[0] && expl[0][0] && expl[0][0].uid == uid) {
      resetError($("#word input"));
      $("#word").draggable().show();
      $("#word>input").val(oldWord).autocomplete("search");
    }
    return false;
  }).contextmenu(function() {
    $(this).dblclick();
    return false;
  });

  $("#t3").dblclick(function() {
    $("#tab3").click();
    return false;
  }).contextmenu(function() {
    $(this).dblclick();
    return false;
  });
 
  $("#tab3").click(function() {
    if (uid) edit(2,-1);
  });

  $("#t4").click(function() {
    $.post("getHelp.php", function(help) {
      $("#tab4").html(convertText(help));
    });
  }).contextmenu(function() {
    $(this).dblclick();
    return false;
  });

  $("#t4,#tab4").dblclick(function() {
    if (uid) {
      if (word === "?") {
        $("#t3").click();
      } else {
        findWord("?", true);
      }
    } else {
      $(this).click();
    }
    return false;
  });

  $("#tab1").on("click", "#w-nick", function() {
    resetError($("#editor input"));
    $("#editor").focus().draggable().show();
  }).on("click", "#w-tell", function() {
    var o = $(this);
    var e = expl[0][0];
    var t = (e.tell + 1) % 3;
    $.post("updateTell.php", {wid: e.wid, tell: t}, function(rc) {
      if (rc == '1') {
        o.html(tellName(e.tell = t));
        showCount([4, 5]);
      } else {
        info("updateTell.php: "+ rc);
      }
    });
  });

  $("#tab1,#viewer").on("click", "[data-l]", function() {
    findWord($(this).attr("data-l"));
    $("#t1").click();
  });

  $("#word,#editor").on("click", ".x-close", function() {
    $(this).parent().hide().children("input").blur();
  });

  $("#tab2").on("click", ".a-view", function() {  // 보기
    var o = $(this).parent().parent(); 
    var i = o.index()/2;
    var e = expl[0][i+1];
    viewIt(e.t, e.nick, e.data, "#t2");
    o.parent().accordion("option", "active", i);
  }).on("click", ".a-accept", function() {        // 올림
    accept($(this).parent().parent().index()/2 + 1, true);
  });

  $("#viewer").dblclick(function() {
    if ($("#viewer").data()[0] === "#t2") {
      edit(0, 1 + $("#tab2").accordion("option", "active"));
    }
    $("#t5").click();
  }).on("click", ".ui-accordion-header", function() {
    $($(this).parent().data()[0]).click();
  });

  $("#tab1,#tab2,#tab3").on("click", ".a-edit", function() {
    var o = $(this).parent().parent();
    edit(o.parent().index() - 1, o.index()/2);
    o.parent().accordion("option", "active", o.index()/2);
    return false;
  }).on("dblclick", ".ui-accordion-content", function() {
    edit($(this).parent().index() - 1, ($(this).index() - 1)/2);
    return false;
  }).on("click", ".ui-accordion-content", function() {
    return false;
  });

  $("#tab2,#tab3").on("click", ".ui-accordion-header", function() {
    var t = $(this), o = t.parent();
    o.find(".a-head").show();
    o.find(".a-cmd").hide();
    if (o.accordion("option", "active") !== false) {
      t.find(".a-cmd").show();
      t.find(".a-head").hide();
    }
    return false;
  });

  $("#word>input").keydown(function(e) {
    if (e.keyCode === $.ui.keyCode.ENTER) $(this).blur();
  }).autocomplete({source: "searchWord.php"})
    .blur(function() { newWord(false); });

  $("#editor>input").keydown(function(e) {
    if (e.keyCode === $.ui.keyCode.ENTER) $(this).blur();
  }).autocomplete({source: "searchUser.php"})
    .blur(function() { newEditor(false); });

  $("#word > .b-shade").click(function() {   newWord(true); });
  $("#editor>.b-shade").click(function() { newEditor(true); });

  function newWord(isUpdate) {
    var o = $("#word>input"), oVal = o.val().trim();
    o.val(oVal);
    resetError(o);
    if (!oVal || oVal == word) {
      if (isUpdate) o.parent().hide(); 
    } else {
      var arg = o.serialize();
      if (isUpdate) arg += "&wid="+ expl[0][0].wid;
      $.post("updateWord.php", arg, function(code) {
        if (code == '1') {
          findWord(oVal, true);
          o.parent().hide();
        } else if (code == '2') {
          setError(o, "이 올림말은 있습니다.");
        } else if (code != '3') {
          info("updateWord.php: "+ code);
        }
      });
    }
  }

  function newEditor(isUpdate) {
    var e = expl[0][0];
    var o = $("#editor>input"), oVal = o.val().trim();
    o.val(oVal);
    resetError(o);
    if (!oVal || o.val() == e.nick) {
      if (isUpdate) o.parent().hide();
    } else {
      var arg = o.serialize();
      if (isUpdate) arg += "&uid="+ uid +"&wid="+ e.wid;
      $.post("updateEditor.php", arg, function(code) {
        if (code == '1') {
          findWord();
          o.parent().hide();
        } else if (code == '2') {
          setError(o, "등록되지 않았습니다.");
        } else if (code != '3') {
          info("updateEditor.php: "+ code);
        }
      });
    }
  }

  function accept(j, isAccept) {
    var e = expl[0][j];
    var userId = e.uid == uid || word == '?'? '-': uid;
    $.post("updateData.php", {i:0, id: e.id, user: userId}, function(count) {
      if (count == '1') {
        if (isAccept) findWord(word, true);
      } else {
        info("updateData.php: "+ count);
      }
    });
  }

  $("#edit").val(eEdit.data = "");

  $("#count1").data({ s:"#count1", x:"@", a:"b-yellow" });
  $("#count2").data({ s:"#count2", x:"#", a:"b-green" });
  $("#count3").data({ s:"#count3", x:"$", a:"b-white" });
  $("#count4").data({ s:"#count4", x:"^", a:"b-yellow" });
  $("#count5").data({ s:"#count5", x:"&", a:"b-yellow" });

  $("#count1,#count2,#count3,#count4,#count5").click(function() {
    var o = $(this), data = o.data();
    $.post("getC"+ data.s.substring(2) +"a.php", function(array) {
      var tbody = $("#editors tbody").empty(), sum = 0;
      for (var i = 0, len = array.length; i < len; i++) {
        var a = array[i];
        tbody.append($("<tr>").data([data.x + a[0]])
             .append($("<td>"+ a[0] +"</td><td><i>"+ a[1] +"</i></td>")));
        sum += parseInt(a[1]);
      }
      o.text(sum);
      $("#editors thead td").removeClass().addClass(data.a);
      $("#editors").show()
               .position({ my:"right top", at:"right+8 bottom+2", of:data.s });
    }, 'json');
    return false;
  });

  $("#h1").click(function() {
    if ($("#exit").is(":visible")) {
      $.post("getUsers.php", function(array) {
        var o = $("#list tbody").empty().data([false, false, true, false]);
        $("#list sup:eq(0)").text(array.length);
        $("#open-note").hide();
        for (var i = 0, len = array.length; i < len; i++) {
          var a = array[i];
          var checkbox = a[3] == uid? "": "<input type='checkbox'>";
          var row = $("<tr><td>"+ a[0] +"</td><td>"+ checkbox +"</td><td>"+
                        a[1] +"</td><td title='보증인'>"+ a[2] +"</td></tr>");
          row.data([a[3]]);
          o.append(row);
        }
        $("#list").show().focus();
      }, 'json');
    }
    return false;
  });

  $("#ans     .x-close").click(function() { $("#ans").hide(); });
  $("#toSure  .x-close").click(function() { $("#toSure").hide(); });
  $("#list    .x-close").click(function() { $("#list").hide(); });
  $("#editors .x-close").click(function() { $("#editors").hide(); });

  $("#editors>tbody").on("click", "tr", function() {
    $("#arg").val($(this).data()[0]).autocomplete("search");
  }).on("mouseenter", "tr", function() {
    this.classList.add("b_grey");
  }).on("mouseleave", "tr", function() {
    this.classList.remove("b_grey");
  });

  function setListCheckbox(b) {
    $("#list tbody").find("[type=checkbox]").prop("checked", b);
    countMailTo();
  }

  $("#list thead [type=checkbox]").change(function() {
    setListCheckbox($(this).prop("checked"));
  });

  $("#list tbody").on('change', '[type=checkbox]', function() {
    countMailTo();
  });

  function countMailTo() {
    var count = $("#list tbody input:checked").length;
    document.getElementById('open-note').style.display = count? 'inline': 'none';
    if (count === 0) hideNoteForm();
    $("#open-note sup").text(count);
  }

  function hideNoteForm() {
    $($("#note-form").hide().data()[1] + " .x-close").show();    
  }

  $("#open-note").click(function() {
    if (!$("#note-form").is(":visible")) {
      $("#list .x-close").hide();
      $("#send-note").show();
      $("#note-form").width($("#list").width()).draggable({ cursor:"move" })
        .show().position({ my:"left top", at:"left-1 top", of:"#list tbody" })
        .data([function() {
          var to = '';
          $("#list tbody>tr input:checked").each(function() {
            to += ','+ $(this).parent().parent().data()[0];
          });
          return to.substr(1);
        }, "#list"]);
      setNoteSize();
    }
    return false;
  });

  $("#note-form .x-close").click(function() {
    hideNoteForm();
  });

  $("#send-note").click(function() {
    var subj = $("#subj").val().trim();         // 제목
    var note = $("#note").val().trim();         // 본문
    if (subj || note) {
      $(this).hide();
      var  arg = {re:nick, rea:mail, atts:''};  // 발신인, 발신인 주소, 첨부
      arg.ids  = $("#note-form").data()[0]();   // 수신인 id 배열
      arg.subj = subj? subj: nick +" 님이 알립니다";             // 새 제목
      arg.note = "(글쓴이: "+ nick +" <"+ mail +">)\n\n"+ note;  // 새 본문
      var atts = [];                            // 첨부 데이터 배열
      $("#atts li").each(function() {
        atts.push($(this).data());
      });
      if (atts.length) {
        arg.atts = atts;
      }
      $.post("sendMail.php", arg, function(rc) {
        if (rc) info("sendMail.php: "+ rc);
        hideNoteForm();
      });
    } else {
      $("#subj").focus();
    }
  });

  function size(n) {
    if (1000 <= n) {
      n /= 1000;
      var unit = 'K';
      if (1000 <= n) {
        n /= 1000;
        unit = 'M';
      }
      return n.toFixed(n < 10? 1: 0) + unit;
    }
    return n;
  }

  var dropZone = document.getElementById('note-form');

  dropZone.addEventListener('drop', function(e) {
    e.stopPropagation();
    e.preventDefault();
    var atts = $("#atts");
    var totalSize = 0;
    atts.children().each(function() {
      totalSize += $(this).data().size;
    });
    var filelist = e.dataTransfer.files;
    for (var i = 0, f; f = filelist[i]; i++) {
      if (totalSize + f.size < MAX_MAIL_SIZE) {
        totalSize += f.size;
        fill(f);
      }
    }

    function fill(f) {
      var reader = new FileReader();
      reader.onload = function(e) {
        var att = $('<li><strong>'+ f.name +'</strong> '+ size(f.size) +'</li>');
        var data = window.btoa(reader.result);
        atts.append(att.data({name:f.name, size:f.size, data:data}));
        setNoteSize();
      };
      reader.readAsBinaryString(f);
    }
  }, false);

  dropZone.addEventListener('dragover', function(e) {
    e.stopPropagation();
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
  }, false);

  $("#atts").on("click", "li", function() {
    this.remove();
    setNoteSize();
  }).on("mouseenter", "li", function() {
    this.classList.add("b_grey");
  }).on("mouseleave", "li", function() {
    this.classList.remove("b_grey");
  });

  $("#list tbody").on("click", "td", function() {
    var index = $(this).index();
    if (index != 1) {
      $("#list thead [type=checkbox]").prop("checked", false);
      setListCheckbox(false);
      var tbody = $(this).parents("tbody");
      var  data = tbody.data();
      var  desc = data[index];
      data[index] = !data[index];
      tbody.data(data);
      var  rows = tbody.find("tr").toArray().sort(cmp);
      for (var len = rows.length, r = 0; r < len; r++) {
        tbody.append(rows[r]);
      }
    }
    
    function cmp(row1, row2) {
      var diff = cmpText(index);
      if (diff === 0) diff = cmpText(index == 2? 0: 2);
      return desc? -diff: diff;

      function cmpText(columnIndex) {
        var a = text(row1), b = text(row2);
        return $.isNumeric(a) && $.isNumeric(b)? a - b: a.localeCompare(b);
    
        function text(row) {
          return $(row).children("td").eq(columnIndex).text();
        }
      }
    }
  });

  function setNoteSize() {
    var formWidth = $("#note-form").width();
    $("#subj").width(formWidth - 14);
    $("#note").width(formWidth - 11);
    $("#note").height($(window).height() - $("#note").offset().top);
  }

  $(window).resize(function() {
    fitHeaders($("#tab2"));
    fitHeaders($("#tab3"));
    $("#exit").is(":visible") && h1();
    var h = $(window).height();
    $("#tab1,#tab2,#tab3,#tab4,#viewer").height(h - 76);
    $("#edit").height(h - 109);
    $("#list tbody").css("max-height", h - 72);
    $("#editors tbody").css("max-height", h - 60);
    $(".ui-autocomplete").css("max-height", h - 40);
    $("#note-form").is(":visible") && setNoteSize();
  }).resize();

  $("body").css("visibility", "visible").tooltip({ show:false, hide:false });

  function msie(f) {
    var a = navigator.userAgent;
    var i = a.indexOf('MSIE ');
    if (0 < i && (i = parseInt(a.substring(i + 5))) < 11) f(i);
  }

  msie(function(i) {
    info("인터넷익스플로러 "+ i +"에서는 안되는 기능이 더러 있습니다.<br>"+
         "다른 브라우저를 써 보세요: "+
    "<a href='https://www.google.com/chrome/' target='_blank'>크롬</a>, "+
    "<a href='http://www.opera.com/ko' target='_blank'>오페라</a>, "+
    "<a href='https://www.mozilla.org/firefox/new/' target='_blank'>파이어폭스</a>.");
    if (!Array.prototype.indexOf) {
      Array.prototype.indexOf = function(obj, start) {
        for (var i = (start || 0), j = this.length; i < j; i++) {
          if (this[i] === obj) return i;
        }
        return -1;
      };
    }
    if (!String.prototype.trim) {
      String.prototype.trim = function() {
        return this.replace(/^\s+/, '').replace(/\s+$/, '');
      };
    }
  });
});
