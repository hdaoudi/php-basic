<?
//date in dutch~
//$arraymaand=array("Januari","Februari","Maart","April","Mei","Juni","Juli","Augustus","September","Oktober","November","December");
//$datum=date("j ") . $arraymaand[date("n") - 1] . date(" Y");
$date=date("j ") . date("F ").date(" Y");
$time=(strftime("%H.%M"));


class mysql{

	var $obj = array	(	"mysql_host"	=> "localhost",
							"mysql_user"	=> "root",
							"mysql_db"		=> "exercise_test",
							"mysql_pass"	=> "root",
						);

	var $connection_id	= "",
		$query_id		= "",
		$num_rows		= "";
	var $fetched_obj;
	var $fetched_array;
	var $fetched_row;
	var $affected_rows;

	function mysqlconnect(){
		$this->connection_id = mysql_connect($this->obj['mysql_host'], $this->obj['mysql_user'], $this->obj['mysql_pass']);
		if (!mysql_select_db($this->obj['mysql_db'], $this->connection_id)){
            echo ("Could not find database <b>".$this->obj['mysql_db']."</b>. ");
        }
    }
	function mysql_exit(){
		mysql_close($this->connection_id) or die(mysql_error());
	}
	function query($query){
		$this->mysqlconnect();
		$this->query_id = mysql_query($query, $this->connection_id) or die(mysql_error());
	}
	function fetch_row($query_id=""){
		if($query_id==""){
			$query_id=$this->query_id;
		}
		$this->fetched_row=mysql_fetch_row($query_id);
		return $this->fetched_row;
	}
	function fetch_object($query_id=""){
		if($query_id==""){
			$query_id=$this->query_id;
		}
		$this->fetched_obj=mysql_fetch_object($query_id);
		return $this->fetched_obj;
	}
	function fetch_array($query_id=""){
		if($query_id==""){
			$query_id=$this->query_id;
		}
		$this->fetched_array=mysql_fetch_object($query_id);
		return $this->fetched_array;
	}
	function get_num_rows(){
		$this->num_rows=mysql_num_rows($this->query_id);
		return $this->num_rows;
    }
	function affected_rows(){
		$this->affected_rows=mysql_affected_rows($this->query_id);
	}
}

class user{
	var	$user_id,
		$user_name,
		$user_password,
		$user_level,
		$user_email,
		$user_homepage;
	function user_register($user_name,$user_password,$user_email,$user_homepage){
		$conn=new mysql;
		$conn->query("SELECT * FROM users WHERE user_name='$user_name' OR user_email='user_email'");
		$email=new email;
		if((!isset($user_name)) || (!isset($user_password)) || (!isset($user_email)) || ($user_password!=$user_password2) || ($conn->get_num_rows() > 0) || ($email->checkemail==0)) die ("ERROR FILL ALL FIELDS CORRECT");
		else {
			$conn->query("INSERT INTO users VALUES(NULL,'$user_name','md5($user_password)','0','$user_email','$user_homepage')");
			$this->user_id=mysql_insert_id();
			$this->user_name=$user_name;
			$this->user_password=$user_password;
			$this->user_level=0;
			$this->user_email=$user_email;
			$this->user_homepage=$user_homepage;
		}
		$conn->mysql_exit();
	}
	function verify_password($user_name,$user_password){
		$this->user_name=$username;
		$this->user_password=$user_password;
		$conn=new mysql;
		$conn->query("SELECT user_password FROM users WHERE user_name='$this->user_name'");
		$actual_password=mysql_result($result,0);
		if(!($actual_password == $this->user_password)) die ("Incorrect Password.");
		else {
			$login="ok";
			$_SESSION['login']=$login;
		}
		$conn->mysql_exit();
	}
	function lost_password($user_email){
		$conn=new mysql;
		$conn->query("SELECT * FROM users WHERE user_email='$user_email'");
		if($conn->get_num_rows()==0) die ("NO RECORD FOUND");
		else {
			$row=$conn->fetch_row();
			$new_password=md5(make_randompassword());
			$conn->query("UPDATE users SET user_password='$new_password' WHERE user_email='$user_email'");
			$message="Hi $username,\n we have reset your password.
			\n
			User name: $row[1]
			New Password: $random_password
			\n
			Thanks!
			$admin_email
			\n
			This is an automated message system, please do not reply!";
			mail("$user_email","New password for $row[1].","$message");
		}
	}
	function make_randompassword() {
		$dic="abchefghjkmnpqrstuvwxyz0123456789";
		srand((double)microtime()*1000000);
		$i=0;
		while($i<=7){
	    	$num=rand() % 33;
	    	$tmp=substr($dic, $num, 1);
			$new_password=$new_password . $tmp;
	    	$i++;
	  	}
		return $new_password;
	}
	function change_password($new_password){
		$conn=new mysql;
		$conn->query("UPDATE users SET user_password='$new_password' WHERE user_name='$this->user_name'");
		$this->user_password=$new_password;
		$conn->mysql_exit();
	}
	function users_get(){
		$conn=new mysql;
		$conn->query("SELECT * FROM users ORDER BY news_id DESC");
		$i=0;
		while($row=$conn->fetch_array()){
			$data[$i]=$row;
			$i++;
		}
		return $data;
		$conn->mysql_exit();
	}
	function logout(){
		if ($_SESSION['login']=="ok"){
			$_SESSION=array();
			session_destroy();
		}
	}
}

class pagenav{
    function pagenav ($total, $perpage, $current, $linkstart){
	/*
		$total = total aantal items (mysql_num_rows)
		$perpage = Het max. aantal items per pagina
		$current = Is de huidige $nav pagina (?nav=2 oid)
		$linkstart = Is wat achter de ? en voor &nav= staat. Voorbeeld: $linkstart = "PageID=1&Mail=ja&Skin=1";
	*/
	$this->total=$total;
	$this->perpage=$perpage;
	if(!$current > 0) $current=1;
	$this->current = $current;
	$this->linkstart = $linkstart;
    }
    function get_startpoint (){
	/*
		Met deze functie krijg je een getal terug wat je voor LIMIT $getal, $aantal kan gebruiken.
	*/
	$p = $this->current - 1;
	$r = $p * $this->perpage;
	return $r;
    }

    function makenav (){
	/*
		Deze functie geeft een string terug met daarin : Vorige | 1 | 2 | 3 | 4 | Volgende
	*/
	$this->pages = $this->total / $this->perpage;
	$this->pages = ceil($this->pages);
	for ($i = 1; $i < $this->pages + 1; $i++){
		if ($this->current == $i) $nav[] = "<B>".$i."</B>";
		else $nav[] = "<a href='?" . $this->linkstart . "&nav=" . $i . "'>".$i."</a>";
	}
	if ($this->current != 1) array_unshift($nav, "<a href='?" . $this->linkstart . "&nav=" . ($this->current - 1) . "'>previous</a>");
	else array_unshift($nav, "previous");
	if ($this->current < $this->pages) $nav[] = "<a href='?" . $this->linkstart . "&nav=" . ($this->current + 1) . "'>next</a>";
	else $nav[] = "next";
	$nav = implode (" | ", $nav);
	return $nav;
    }
}

class news{
	var $news_id,
		$news_subject,
		$news_content,
		$news_date,
		$news_time,
		$news_max=5;
	function news_add($news_subject,$news_content){
		$conn=new mysql;
		$news_date=$GLOBALS[date];
		$news_time=$GLOBALS[time];
		$conn->query("INSERT INTO news VALUES(NULL,'$news_subject','$news_content','$news_date','$news_time')");
		$this->news_id=mysql_insert_id();
		$this->news_subject=$news_subject;
		$this->news_content=$news_content;
		$this->news_date=$news_date;
		$this->news_time=$news_time;
		$conn->mysql_exit();
	}
	function news_edit($news_id,$new_news_subject,$new_news_content){
		$conn=new mysql;
		$conn->query("UPDATE news SET news_subject='$new_subject',news_content='$new_content' WHERE news_id='$news_id'");
		$this->news_subject=$new_news_subject;
		$this->news_content=$new_news_content;
		$conn->mysql_exit();
	}
	function news_del($news_id){
		$conn=new mysql;
		$conn->query("DELETE FROM news WHERE news_id='$news_id'");
		$this->news_id="0";
		$conn->mysql_exit();
	}
	function news_get(){
		$conn=new mysql;
		$conn->query("SELECT * FROM news ORDER BY news_id DESC");
		$i=0;
		while($row=$conn->fetch_array()){
			$data[$i]=$row;
			$i++;
		}
		return $data;
		$conn->mysql_exit();
	}
}

class guestbook{
	var $gb_id,
		$user_id,
		$gb_content,
		$gb_date,
		$gb_time;
	function gb_add($user_name,$gb_content){
		$conn=new mysql;
		$gb_date=$GLOBALS[date];
		$gb_time=$GLOBALS[time];
		$conn->query("INSERT INTO guestbook VALUES(NULL,'$user_id','$gb_content','$gb_date','$gb_time')");
		$this->gb_id=mysql_insert_id();
		$this->user_id=$user_id;
		$this->gb_content=$gb_content;
		$this->gb_date=$gb_date;
		$this->gb_time=$gb_time;
		$conn->mysql_exit();
	}
	function gb_edit($gb_id,$new_content){
		$conn=new mysql;
		$conn->query("UPDATE guestbook SET gb_content='$new_content' WHERE gb_id='$gb_id'");
		$this->gb_content=$new_content;
		$conn->mysql_exit();
	}
	function gb_del($gb_id){
		$conn=new mysql;
		$conn->query("DELETE FROM guestbook WHERE gb_id='$gb_id'");
		$this->gb_id="0";
		$conn->mysql_exit();
	}
	function gb_get(){
		$conn=new mysql;
		$conn->query("SELECT * FROM guestbook ORDER BY gb_id DESC");
		$i=0;
		while($row=$conn->fetch_array()){
			$data[$i]=$row;
			$i++;
		}
		return $data;
		$conn->mysql_exit();

	}
}

class comments{
	var $comment_id,
		$news_id,
		$user_id,
		$comment_content,
		$comment_date,
		$comment_time;
	function comment_add($news_id,$user_name,$comment_content){
		$conn=new mysql;
		$comment_date=$GLOBALS[date];
		$comment_time=$GLOBALS[time];
		$conn->query("INSERT INTO comments VALUES(NULL,'$news_id','$user_id','$comment_content','$GLOBALS[date]','$GLOBALS[time]')");
		$this->comment_id=mysql_insert_id();
		$this->news_id=$news_id;
		$this->user_id=$user_id;
		$this->comment_content=$comment_content;
		$this->comment_date=$comment_date;
		$this->comment_time=$comment_time;
		$conn->mysql_exit();
	}
	function comment_edit($comment_id,$new_content){
		$conn=new mysql;
		$conn->query("UPDATE comments SET comment_content='$new_content' WHERE comment_id='$comment_id'");
		$this->comment_content=$new_content;
		$conn->mysql_exit();
	}
	function comment_del($comment_id){
		$conn=new mysql;
		$conn->query("DELETE FROM comments WHERE comment_id='$comment_id'");
		$this->comment_id="0";
		$conn->mysql_exit();
	}
	function comment_get(){
		$conn=new mysql;
		$conn->query("SELECT * FROM comments ORDER BY comment_id DESC");
		$i=0;
		while($row=$conn->fetch_array()){
			$data[$i]=$row;
			$i++;
		}
		return $data;
		$conn->mysql_exit();
	}
}

class email{
	var $name,
		$email,
		$message,
		$my_email="hass@pandora.be";
	
	function checkemail($email){
		return  ereg("[A-Za-z0-9_-]+([\.]{1}[A-Za-z0-9_-]+)*@[A-Za-z0-9-]+([\.]{1}[A-Za-z0-9-]+)+",$email);
	}

	function email_form($name,$email,$message){
		if (($name== "") || ($email=="") || ($message=="")) die ("Please, fill in all fields.");
		if ($this->checkemail($email)=="") die ("Please, fill a correct e-mailadresse in.");
		$this->name=$name;
		$this->email=$email;
		$this->message=$message;
		mail("$this->my_email","$name", "name: $name\ne-mail: $email\nmessage: $message");
	}
}

///bbcode class by Leif K-Brooks
class bbcode{
	var $tags,
		$settings;

	function begtoend($htmltag){
		return preg_replace('/<([A-Za-z]+)>/','</$1>',$htmltag);
	}
	function replace_pcre_array($text,$array){
		$pattern = array_keys($array);
		$replace = array_values($array);
		$text = preg_replace($pattern,$replace,$text);
		return $text;
	}
	function bbcode(){
		$this->tags = array();
		$this->settings = array('enced'=>true);
	}
	function get_data($name,$cfa = ''){
		if(!array_key_exists($name,$this->tags)) return '';
		$data = $this->tags[$name];
		if($cfa) $sbc = $cfa; else $sbc = $name;
		if(!is_array($data)){
			$data = preg_replace('/^ALIAS(.+)$/','$1',$data);
			return $this->get_data($data,$sbc);
		}else{
			$data['Name'] = $sbc;
			return $data;
		}
	}
	function change_setting($name,$value){
		$this->settings[$name] = $value;
	}
	function add_alias($name,$aliasof){
		if(!array_key_exists($aliasof,$this->tags) or array_key_exists($name,$this->tags)) return false;
		$this->tags[$name] = 'ALIAS'.$aliasof;
		return true;
	}
	function onparam($param,$regexarray){
		$param = $this->replace_pcre_array($param,$regexarray);
		if(!$this->settings['enced']){
			$param = htmlentities($param);
		}
		return $param;
	}
	function export_definition(){
		return serialize($this->tags);
	}
	function import_definiton($definition,$mode = 'append'){
		switch($mode){
			case 'append':
			$array = unserialize($definition);
			$this->tags = $array + $this->tags;
			break;
			case 'prepend':
			$array = unserialize($definition);
			$this->tags = $this->tags + $array;
			break;
			case 'overwrite':
			$this->tags = unserialize($definition);
			break;
			default:
			return false;
		}
		return true;
	}
	function add_tag($params){
		if(!is_array($params)) return 'Paramater array not an array.';
		if(!array_key_exists('Name',$params) or empty($params['Name'])) return 'Name parameter is required.';
		if(preg_match('/[^A-Za-z]/',$params['Name'])) return 'Name can only contain letters.';
		if(!array_key_exists('HasParam',$params)) $params['HasParam'] = false;
		if(!array_key_exists('HtmlBegin',$params)) return 'HtmlBegin paremater not specified!';
		if(!array_key_exists('HtmlEnd',$params)){
			 if(preg_match('/^(<[A-Za-z]>)+$/',$params['HtmlBegin'])){
			 	$params['HtmlEnd'] = $this->begtoend($params['HtmlBegin']);
			 }else{
			 	return 'You didn\'t specify the HtmlEnd parameter, and your HtmlBegin parameter is too complex to change to an HtmlEnd parameter.  Please specify HtmlEnd.';
			 }
		}
		if(!array_key_exists('ParamRegexReplace',$params)) $params['ParamRegexReplace'] = array();
		if(!array_key_exists('ParamRegex',$params)) $params['ParamRegex'] = '[^\\]]+';
		if(!array_key_exists('HasEnd',$params)) $params['HasEnd'] = true;
		if(array_key_exists($params['Name'],$this->tags)) return 'The name you specified is already in use.';
		$this->tags[$params['Name']] = $params;
		return '';
	}
	function parse_bbcode($text){
		$this->add_default_tags();
		foreach($this->tags as $tagname => $tagdata){
			if(!is_array($tagdata)) $tagdata = $this->get_data($tagname);
			$startfind = "/\\[{$tagdata['Name']}";
			if($tagdata['HasParam']){
				$startfind.= '=('.$tagdata['ParamRegex'].')';
			}
			$startfind.= '\\]/';
			if($tagdata['HasEnd']){
				$endfind = "[/{$tagdata['Name']}]";
				$starttags = preg_match_all($startfind,$text,$ignore);
				$endtags = substr_count($text,$endfind);
				if($endtags < $starttags){
					$text.= str_repeat($endfind,$starttags - $endtags);
				}
				$text = str_replace($endfind,$tagdata['HtmlEnd'],$text);
			}
			$replace = str_replace(array('%%P%%','%%p%%'),'\'.$this->onparam(\'$1\',$tagdata[\'ParamRegexReplace\']).\'','\''.$tagdata['HtmlBegin'].'\'');
			$text = preg_replace($startfind.'e',$replace,$text);
		}
		return $text;
	}
	function add_default_tags(){
		$this->add_tag(array('Name'=>'b','HtmlBegin'=>'<span style="font-weight: bold;">','HtmlEnd'=>'</span>'));
		$this->add_tag(array('Name'=>'i','HtmlBegin'=>'<span style="font-style: italic;">','HtmlEnd'=>'</span>'));
		$this->add_tag(array('Name'=>'u','HtmlBegin'=>'<span style="text-decoration: underline;">','HtmlEnd'=>'</span>'));
		$this->add_tag(array('Name'=>'link','HasParam'=>true,'HtmlBegin'=>'<a href="%%P%%">','HtmlEnd'=>'</a>'));
		$this->add_tag(array('Name'=>'color','HasParam'=>true,'ParamRegex'=>'[A-Za-z0-9#]+','HtmlBegin'=>'<span style="color: %%P%%;">','HtmlEnd'=>'</span>','ParamRegexReplace'=>array('/^[A-Fa-f0-9]{6}$/'=>'#$0')));
		$this->add_tag(array('Name'=>'email','HasParam'=>true,'HtmlBegin'=>'<a href="mailto:%%P%%">','HtmlEnd'=>'</a>'));
		$this->add_tag(array('Name'=>'size','HasParam'=>true,'HtmlBegin'=>'<span style="font-size: %%P%%pt;">','HtmlEnd'=>'</span>','ParamRegex'=>'[0-9]+'));
		$this->add_tag(array('Name'=>'bg','HasParam'=>true,'HtmlBegin'=>'<span style="background: %%P%%;">','HtmlEnd'=>'</span>','ParamRegex'=>'[A-Za-z0-9#]+'));
		$this->add_tag(array('Name'=>'s','HtmlBegin'=>'<span style="text-decoration: line-through;">','HtmlEnd'=>'</span>'));
		$this->add_tag(array('Name'=>'align','HtmlBegin'=>'<div style="text-align: %%P%%">','HtmlEnd'=>'</div>','HasParam'=>true,'ParamRegex'=>'(center|right|left)'));
		$this->add_alias('url','link');
	}
}
?>