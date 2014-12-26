<?
require_once("bootstrap.inc.php");
require_once("include_generic/recaptchalib.php");
require_once("include_generic/countries.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/default_usersettings.php");

$COUNTRIES = array_merge(array(""),$COUNTRIES);

$avatars = glob(POUET_CONTENT_LOCAL."avatars/*.gif");

$success = null;

$namesNumeric = array(
  // numbers
  "indextopglops" => "front page - top glops",
  "indextopprods" => "front page - top prods (recent)",
  "indextopkeops" => "front page - top prods (all-time)",
  "indexoneliner" => "front page - oneliner",
  "indexlatestadded" => "front page - latest added",
  "indexlatestreleased" => "front page - latest released",
  "indexojnews" => "front page - bitfellas news",
  "indexlatestcomments" => "front page - latest comments",
  "indexlatestparties" => "front page - latest parties",
  "indexbbstopics" => "front page - bbs topics",
  "indexwatchlist" => "front page - watchlist",
  "bbsbbstopics" => "bbs page - bbs topics",
  "prodlistprods" => "prodlist page - prods",
  "userlistusers" => "userlist page - users",
  "searchprods" => "search page - prods",
  "userlogos" => "user page - logos",
  "userprods" => "user page - prods",
  "usergroups" => "user page - groups",
  "userparties" => "user page - parties",
  "userscreenshots" => "user page - screenshots",
  "usernfos" => "user page - nfos",
  "usercomments" => "user page - comments",
  "userrulez" => "user page - rulez",
  "usersucks" => "user page - sucks",
  "commentshours" => "comments page - hours",
  "topicposts" => "topic page - posts",
);
$namesSwitch = array(
  //select
  "logos" => "logos",
  "topbar" => "top bar",
  "bottombar" => "bottom bar",
  "indexcdc" => "front page - cdc",
  "indexsearch" => "front page - search",
  "indexstats" => "front page - stats",
  "indexlinks" => "front page - links",
  "indexplatform" => "front page - show platform icons",
  "indextype" => "front page - show type icons",
  "indexwhoaddedprods" => "front page - who added prods",
  "indexwhocommentedprods" => "front page - who commented prods",
  "topichidefakeuser" => "bbs page - hide fakeuser",
  "prodhidefakeuser" => "prod page - hide fakeuser",
  "displayimages" => "[img][/img] tags should be displayed as...",
  "indexbbsnoresidue" => "residue threads on the front page are...",
);

class PouetBoxAccount extends PouetBox
{
  function PouetBoxAccount( )
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_account";
    $this->title = "e-e-e-edit your account";
    $this->formifier = new Formifier();
  }

  function LoadFromDB()
  {
    global $COUNTRIES;
    $this->cdcs = array();
    global $sceneID;

    $query = new BM_Query( "users" );
    $query->AddExtendedFields();
//      foreach(PouetUser::getExtendedFields() as $v)
//        $query->AddField("users.".$v);
    $query->AddWhere(sprintf_esc("users.id = %d",get_login_id()));
    $query->SetLimit(1);

    $s = $query->perform();
    $this->user = reset( $s );

    $this->sceneID = $this->user->GetSceneIDData( false );
    
    $rows = SQLLib::SelectRows(sprintf_esc("select cdc from users_cdcs where user=%d",get_login_id()));
    foreach($rows as $r)
      $this->cdcs[] = $r->cdc;
      
    /*
    $this->fieldsSceneID = array(
      "login"=>array(
        "info"=>"which word can you type very fast ?",
        "value"=> $this->sceneID["first_name"]." ".$this->sceneID["last_name"],
      ),
      "captcha"=>array(
        "info"=>"real sceners are proficient in the skill of reading letters",
        "name"=>"captcha thingy",
        "type"=>"captcha",
      ),
    );
    */
    
    $this->fieldsPouet = array(
      "nickname"=>array(
        "info"=>"how do you look on IRC ?",
        "required"=>true,
        "value"=>$this->user->nickname,
      ),
      "im_type"=>array(
        "info"=>"the one you really use",
        "name"=>"instant messenger type",
        "type"=>"select",
        "value"=>$this->user->im_type,
      ),
      "im_id"=>array(
        "info"=>"buuuuuuuuuuuuuuuu .... hiho !",
        "name"=>"instant messenger id",
        "value"=>$this->user->im_id,
      ),
      "avatar"=>array(
        "info"=>"your faaaaaaace is like a song",
        "required"=>true,
        "value"=>$this->user->avatar,
        "type"=>"avatar",
      ),
      "slengpung"=>array(
        "info"=>"your slengpung id, if you have one",
        "value"=>$this->user->slengpung,
      ),
      "csdb"=>array(
        "info"=>"your csdb id, if you have one",
        "value"=>$this->user->csdb,
      ),
      "zxdemo"=>array(
        "info"=>"your zxdemo id, if you have one",
        "value"=>$this->user->zxdemo,
      ),
      "demozoo"=>array(
        "info"=>"your demozoo id, if you have one",
        "value"=>$this->user->demozoo,
      ),
    );
    $this->fieldsCDC = array();

    //$this->fieldsSceneID["login"]["type"] = "static";
    //unset($this->fieldsSceneID["captcha"]);

    $glop = POUET_CDC_MINGLOP;
    for ($x=1; $x < 10; $x++)
    {
      /*
      $cdcText = array(
        "your favorite",
        "you love this when you're drunk",
        "the one on that weird platform",
        "",
        "",
        "",
        "",
        "",
        "",
        "",
      );
      */

      if ($this->user->glops >= $glop)
      {
        $this->fieldsCDC["cdc".$x] = array(
          "value" => $this->cdcs[$x-1],
          "name" => "coup de coeur ".$x,
          "info" => $cdcText[$x], // is this cool?
        );
      }
      $glop *= 2;
    }

    global $DEFAULT_USERSETTINGS;
    global $namesNumeric;
    global $namesSwitch;

    $this->fieldsSettings = array();
    foreach(get_object_vars($DEFAULT_USERSETTINGS) as $k=>$v)
    {
      $this->fieldsSettings[$k] = array();
      $this->fieldsSettings[$k]["value"] = $_SESSION["settings"] ? $_SESSION["settings"]->$k : $v;
      if ($namesNumeric[$k])
      {
        $this->fieldsSettings[$k]["name"] = $namesNumeric[$k];
        $this->fieldsSettings[$k]["type"] = "number";
        $this->fieldsSettings[$k]["min"] = strpos($k,"index") === 0 ? 0 : 1;
        $this->fieldsSettings[$k]["max"] = POUET_CACHE_MAX;
      }
      if ($namesSwitch[$k])
      {
        $this->fieldsSettings[$k]["name"] = $namesSwitch[$k];
        $this->fieldsSettings[$k]["type"] = "select";
        $this->fieldsSettings[$k]["assoc"] = true;
        $this->fieldsSettings[$k]["fields"] = array(0=>"hidden",1=>"displayed");
      }
    }
    // exceptions!
    $this->fieldsSettings["topicposts"]["min"] = 1;
    $this->fieldsSettings["indexojnews"]["max"] = 10;
    $this->fieldsSettings["displayimages"]["fields"] = array(0=>"links",1=>"images");
    $this->fieldsSettings["indexbbsnoresidue"]["fields"] = array(0=>"shown",1=>"hidden");

    $this->fieldsSettings["prodcomments"]["name"] = "prod page - number of comments";
    $this->fieldsSettings["prodcomments"]["type"] = "select";
    $this->fieldsSettings["prodcomments"]["assoc"] = true;
    $this->fieldsSettings["prodcomments"]["fields"] = array(-1=>"all",0=>"hide",5=>"5",10=>"10",25=>"25",50=>"50",100=>"100");
    $this->fieldsSettings["prodcomments"]["value"] = $_SESSION["settings"] ? $_SESSION["settings"]->prodcomments : $DEFAULT_USERSETTINGS->prodcomments;

    if ($_POST)
    {
      foreach($_POST as $k=>$v)
      {
        if ($this->fieldsPouet[$k]) $this->fieldsPouet[$k]["value"] = $v;
        if ($this->fieldsCDC[$k]) $this->fieldsCDC[$k]["value"] = $v;
        //if ($this->fieldsSceneID[$k]) $this->fieldsSceneID[$k]["value"] = $v;
        if ($this->fieldsSettings[$k]) $this->fieldsSettings[$k]["value"] = $v;
      }
    }



    $row = SQLLib::SelectRow("DESC users im_type");
    preg_match_all("/'(.*?)'/",$row->Type,$m);
    array_unshift($m[1],"");
    $this->fieldsPouet["im_type"]["fields"] = $m[1];
  }

  function ParsePostLoggedIn( $data )
  {
    global $currentUser;

    $errors = array();

    // cdc bit

    $cdcUnique = array();
    $glop = POUET_CDC_MINGLOP;
    for ($x=1; $x < 10; $x++)
    {
      if ($this->user->glops >= $glop && $data["cdc".$x])
      {
        $cdcUnique[] = $data["cdc".$x];
      }
      $glop *= 2;
    }
    $cdcUnique = array_unique($cdcUnique);
    SQLLib::Query(sprintf_esc("delete from users_cdcs where user = %d",get_login_id()));

    foreach($cdcUnique as $c)
    {
      $a = array();
      $a["user"] = get_login_id();
      $a["cdc"] = $c;
      SQLLib::InsertRow("users_cdcs",$a);
    }

    // pouet bit

    global $avatars;

    $sql = array();
    foreach ($this->fieldsPouet as $k=>$v)
    {
      if ($k == "nickname" && !trim($data[$k])) continue;
      //if (trim($data[$k]))
      $sql[$k] = trim($data[$k]);
    }

    if (!$sql["avatar"] || !file_exists(POUET_CONTENT_LOCAL . "avatars/".$sql["avatar"]))
      $sql["avatar"] = basename( $avatars[ array_rand($avatars) ] );

    SQLLib::UpdateRow("users",$sql,"id=".(int)get_login_id());

    if ($currentUser->nickname != $data["nickname"])
    {
      $a = array();
      $a["user"] = $currentUser->id;
      $a["nick"] = $currentUser->nickname;
      SQLLib::InsertRow("oldnicks",$a);
    }

    // customizer bit

    global $avatars;

    $sql = array();
    foreach ($this->fieldsSettings as $k=>$v)
    {
      if ($v["type"] == "number")
      {
        $sql[$k] = min($v["max"], max($v["min"], (int)($data[$k]) ));
      }
      else
      {
        $sql[$k] = (int)$data[$k];
      }
      $_SESSION["settings"]->$k = (int)$sql[$k];
    }
    if (SQLLib::SelectRow(sprintf_esc("select id from usersettings where id = %d",(int)get_login_id())))
    {
      SQLLib::UpdateRow("usersettings",$sql,"id=".(int)get_login_id());
    }
    else
    {
      $sql["id"] = (int)get_login_id();
      SQLLib::InsertRow("usersettings",$sql);
    }


    global $success;
    if (!$errors) $success = "modifications complete!";

    return $errors;
  }
  use PouetForm;
  function ParsePostMessage( $data )
  {
    if (!get_login_id())
      return array("You have to be logged in!");
      
    $errors = array();

    $data["nickname"] = strip_tags($data["nickname"]);
    $data["nickname"] = trim($data["nickname"]);

    if (strlen($data["nickname"]) < 2)
    {
      $errors[] = "nick too short!";
      return $errors;
    }

    if (!$errors)
    {
      $this->LoadFromDB();
      
      $errors = $this->ParsePostLoggedIn( $data );
    }
//    $this->LoadFromDB();
    return $errors;
  }

  function Render()
  {
    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";
    echo "  <h2>".$this->title."</h2>\n";
    /*
    echo "  <div class='accountsection content'>\n";
    $this->formifier->RenderForm( $this->fieldsSceneID );
    echo "  </div>\n";
    echo "  <h2>pou&euml;t things</h2>\n";
    */
    echo "  <div class='accountsection content'>\n";
    $this->formifier->RenderForm( $this->fieldsPouet );
    echo "  </div>\n";
    if ($this->fieldsCDC)
    {
      echo "  <h2>coup de coeurs</h2>\n";
      echo "  <div class='accountsection content'>\n";
      $this->formifier->RenderForm( $this->fieldsCDC );
      echo "  </div>\n";
    }
    echo "  <h2>sitewide settings</h2>\n";
    echo "  <div class='accountsection content' id='customizer'>\n";
    $this->formifier->RenderForm( $this->fieldsSettings );
    echo "  </div>\n";
    echo "  <div class='foot'><input type='submit' value='Submit' /></div>";
    echo "</div>\n";
  }
};

class PouetBoxAccountModificationRequests extends PouetBox
{
  function PouetBoxAccountModificationRequests( )
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_accountreq";
    $this->title = "your most recent modification requests";
  }
  use PouetForm;
  function LoadFromDB()
  {
    global $currentUser;
    
    $s = new BM_Query("modification_requests");
    $s->AddField("modification_requests.id");
    $s->AddField("modification_requests.requestType");
    $s->AddField("modification_requests.itemID");
    $s->AddField("modification_requests.itemType");
    $s->AddField("modification_requests.requestBlob");
    $s->AddField("modification_requests.requestDate");
    $s->AddField("modification_requests.approved");
    $s->AddField("modification_requests.comment");
    //$s->Attach(array("modification_requests"=>"gloperatorID"),array("users as gloperator"=>"id"));
    $s->Attach(array("modification_requests"=>"itemID"),array("prods as prod"=>"id"));
    $s->AddWhere(sprintf_esc("userID = %d",$currentUser->id));
    $s->AddOrder("requestDate desc");
    $s->SetLimit(10);
    $this->requests = $s->perform();
  }
  function Render()
  {
    global $REQUESTTYPES;
    echo "<table id='".$this->uniqueID."' class='boxtable'>\n";
    echo "  <tr>\n";
    echo "    <th colspan='4'>".$this->title."</th>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <th>date</th>\n";
    echo "    <th>item</th>\n";
    echo "    <th>request</th>\n";
    echo "    <th>approved?</th>\n";
    echo "  </tr>\n";
    foreach($this->requests as $r)
    {
      echo "  <tr>\n";
      echo "    <td>".$r->requestDate."</td>\n";
      echo "    <td>".$r->itemType.": ";
      switch ($r->itemType)
      {
        case "prod": if ($r->prod) echo $r->prod->RenderSingleRowShort();
      }
      echo "</td>\n";
      echo "    <td>".$REQUESTTYPES[$r->requestType]::Describe()."</td>\n";
      echo "    <td>";
      if ( $r->approved === NULL ) echo "<b>pending</b>";
      else if ( $r->approved == 0 ) echo "<b>no</b> :: "._html($r->comment);
      else if ( $r->approved == 1 ) echo "<b>yes</b>";
      echo "</td>\n";
      echo "  </tr>\n";
    }
    echo "</table>\n";
  }
}

///////////////////////////////////////////////////////////////////////////////

if (!get_login_id())
{
  require_once("include_pouet/header.php");
  require("include_pouet/menu.inc.php");

  $message = new PouetBoxModalMessage(false,true);
  $message->classes[] = "errorbox";
  $message->title = "An error has occured:";
  $message->message = "You need to be logged in for this!";
  $message->Render();
}
else
{

  $form = new PouetFormProcessor();
  
  //if (!get_login_id())
  //  $form->successMessage = "registration complete! a confirmation mail will be sent to your address soon - you can't login until you confirmed your email address!";
    
  $form->SetSuccessURL( "index.php", false );
  
  $form->Add( "account", new PouetBoxAccount() );
  $form->Add( "accountReq", new PouetBoxAccountModificationRequests() );
  
  $form->Process();
  
  $TITLE = "account!";
  
  require_once("include_pouet/header.php");
  require("include_pouet/menu.inc.php");
  
  echo "<div id='content'>\n";
  
  $form->Display();
  
  echo "</div>\n";

?>
<script type="text/javascript">
<!--
function updateAvatar()
{
  $("avatarimg").src = "<?=POUET_CONTENT_URL?>avatars/" + $("avatar").options[ $("avatar").selectedIndex ].value;
}
document.observe("dom:loaded",function(){
  if (!$("avatarlist"))
    return;

  var img = new Element("img",{"id":"avatarimg","width":16,"height":16});
  $("avatarlist").insertBefore(img,$("avatar"));
  updateAvatar();
  $("avatarlist").observe("change",updateAvatar);
  $("avatarlist").observe("keyup",updateAvatar);

  var s = new Element("div",{"class":"content","style":"padding:10px;text-align:center;cursor:pointer;cursor:hand;color:#9FCFFF;"}).update("advanced poueteers only - click here to show");
  s.observe("click",function(){ $("customizer").show(); s.hide(); });
  $("customizer").parentNode.insertBefore(s,$("customizer"));
  $("customizer").hide();

  for (var i=1; i<10; i++)
  {
    if (!$("cdc"+i)) continue;
    new Autocompleter($("cdc"+i), {
      "dataUrl":"./ajax_prods.php",
      "width":320,
      "processRow": function(item) {
        var s = item.name.escapeHTML();
        if (item.groupName) s += " <small class='group'>" + item.groupName.escapeHTML() + "</small>";
        return s;
      }
    });
  }
});
//-->
</script>
<?
}

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
