<?php
namespace Manager\Controller;
use Think\Controller;
/*
 * 微信官方API操作
 * 功能 : 微信公众平台API操作
 * 被动回复消息;设置菜单;获取用户列表;获取用户分组等
 * $this->log()函数可以自己写一个调试函数
 * @author zhuwei<zhuwei313@hotmail.com>
 * @version 2.0
 * $Id: Wechat.php 2915 2014-11-12 06:37:11Z A1256 $
 */
class WechatController extends Controller
{
    const MSGTYPE_TEXT = 'text';
    const MSGTYPE_IMAGE = 'image';
    const MSGTYPE_LOCATION = 'location';
    const MSGTYPE_LINK = 'link';
    const MSGTYPE_EVENT = 'event';
    const MSGTYPE_MUSIC = 'music';
    const MSGTYPE_NEWS = 'news';
    const MSGTYPE_VOICE = 'voice';
    const MSGTYPE_VIDEO = 'video';
    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
    const AUTH_URL = '/token?grant_type=client_credential&';
    const MENU_CREATE_URL = '/menu/create?';
    const MENU_GET_URL = '/menu/get?';
    const MENU_DELETE_URL = '/menu/delete?';
    const MEDIA_GET_URL = '/media/get?';
    const QRCODE_CREATE_URL='/qrcode/create?';
    const QR_SCENE = 0;
    const QR_LIMIT_SCENE = 1;
    const QRCODE_IMG_URL='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';
    const SHORT_URL='/shorturl?';
    const USER_GET_URL='/user/get?';
    const USER_INFO_URL='/user/info?';
    const USER_UPDATEREMARK_URL='/user/info/updateremark?'; 
    const GROUP_GET_URL='/groups/get?';
    const USER_GROUP_URL='/groups/getid?';
    const GROUP_CREATE_URL='/groups/create?';
    const GROUP_UPDATE_URL='/groups/update?';
    const GROUP_MEMBER_UPDATE_URL='/groups/members/update?';
    const CUSTOM_SEND_URL='/message/custom/send?';
    const MEDIA_UPLOADNEWS_URL = '/media/uploadnews?';
    const MASS_SEND_URL = '/message/mass/send?';
    const TEMPLATE_SEND_URL = '/message/template/send?';
    const MASS_SEND_GROUP_URL = '/message/mass/sendall?';
    const MASS_DELETE_URL = '/message/mass/delete?';
    const UPLOAD_MEDIA_URL = 'http://file.api.weixin.qq.com/cgi-bin';
    const MEDIA_UPLOAD = '/media/upload?';
    const OAUTH_PREFIX = 'https://open.weixin.qq.com/connect/oauth2';
    const OAUTH_AUTHORIZE_URL = '/authorize?';
    const OAUTH_TOKEN_PREFIX = 'https://api.weixin.qq.com/sns/oauth2';
    const OAUTH_TOKEN_URL = '/access_token?';
    const OAUTH_REFRESH_URL = '/refresh_token?';
    const OAUTH_USERINFO_URL = 'https://api.weixin.qq.com/sns/userinfo?';
    const OAUTH_AUTH_URL = 'https://api.weixin.qq.com/sns/auth?';
    const PAY_DELIVERNOTIFY = 'https://api.weixin.qq.com/pay/delivernotify?';
    const PAY_ORDERQUERY = 'https://api.weixin.qq.com/pay/orderquery?';
    const CUSTOM_SERVICE_GET_RECORD = '/customservice/getrecord?';
    const CUSTOM_SERVICE_GET_KFLIST = '/customservice/getkflist?';
    const CUSTOM_SERVICE_GET_ONLINEKFLIST = '/customservice/getkflist?';
    const SEMANTIC_API_URL= 'https://api.weixin.qq.com/semantic/semproxy/search?';
    
    private $token;
    private $encodingAesKey;
    private $encrypt_type;
    private $appid;
    private $appsecret;
    private $access_token;
    private $user_token;
    private $partnerid;
    private $partnerkey;
    private $paysignkey;
    private $postxml;
    private $_msg;
    private $_funcflag = false;
    private $_receive;
    private $_text_filter = true;
    public $debug =  false;
    public $errCode = 0;
    public $errMsg = "";
    private $_logcallback=array('Yii','log');
    
    public function __construct($options=array())
    {
        //$curr = Yii::app()->session['weaccount'];
        // 结合YII框架写的，非YII的朋友可以修改从SESSION中获取weaccount
        //注释的部分是我用YII框架获取的一些内容，实际开发中可以修改
        //$this->token = isset($options['token'])?$options['token']:(isset($curr['token'])?$curr['token']:'');
        $this->token = isset($options['token'])?$options['token']:'';
        //$this->encodingAesKey = isset($options['encodingaeskey'])?$options['encodingaeskey']:(isset($curr['encodingaeskey'])?$curr['encodingaeskey']:'');
        $this->encodingAesKey = isset($options['encodingaeskey'])?$options['encodingaeskey']:'';
        //$this->appid = isset($options['appid'])?$options['appid']:(isset($curr['appid'])?$curr['appid']:'');
        $this->appid = isset($options['appid'])?$options['appid']:'';
        //$this->appsecret = isset($options['appsecret'])?$options['appsecret']:(isset($curr['appsecret'])?$curr['appsecret']:'');
        $this->appsecret = isset($options['appsecret'])?$options['appsecret']:'';
        //$this->partnerid = isset($options['partnerid'])?$options['partnerid']:(isset($curr['partnerid'])?$curr['partnerid']:'');
        $this->partnerid = isset($options['partnerid'])?$options['partnerid']:'';
        //$this->partnerkey = isset($options['partnerkey'])?$options['partnerkey']:(isset($curr['partnerkey'])?$curr['partnerkey']:'');
        $this->partnerkey = isset($options['partnerkey'])?$options['partnerkey']:'';
        //$this->paysignkey = isset($options['paysignkey'])?$options['paysignkey']:(isset($curr['paysignkey'])?$curr['paysignkey']:'');
        $this->paysignkey = isset($options['paysignkey'])?$options['paysignkey']:'';
        //$this->debug = isset($options['debug'])?$options['debug']:YII_DEBUG;
        $this->debug = isset($options['debug'])?$options['debug']:'';
        //$this->_logcallback = isset($options['logcallback'])?$options['logcallback']:$this->_logcallback;
        $this->_logcallback = isset($options['logcallback'])?$options['logcallback']:$this->_logcallback;
    }
    
    /**
     * For weixin server validation 
     */ 
    private function checkSignature($str='')
    {
        $signature = isset($_GET["signature"])?$_GET["signature"]:'';
        $signature = isset($_GET["msg_signature"])?$_GET["msg_signature"]:$signature; //如果存在加密验证则用加密验证段
        $timestamp = isset($_GET["timestamp"])?$_GET["timestamp"]:'';
        $nonce = isset($_GET["nonce"])?$_GET["nonce"]:'';
                
        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce,$str);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * For weixin server validation 
     * @param bool $return 是否返回
     */
    public function valid($return=false)
    {
        $encryptStr="";
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $postStr = file_get_contents("php://input");
            $array = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->encrypt_type = isset($_GET["encrypt_type"]) ? $_GET["encrypt_type"]: '';
            if ($this->encrypt_type == 'aes') { //aes加密
                //$this->log($postStr);
                $encryptStr = $array['Encrypt'];
                $pc = new Prpcrypt($this->encodingAesKey);
                $array = $pc->decrypt($encryptStr,$this->appid);
                if (!isset($array[0]) || ($array[0] != 0)) {
                    if (!$return) {
                        die('decrypt error!');
                    } else {
                        return false;
                    }
                }
                $this->postxml = $array[1];
                if (!$this->appid)
                    $this->appid = $array[2];//为了没有appid的订阅号。
            } else {
                $this->postxml = $postStr;
            }
        } elseif (isset($_GET["echostr"])) {
            $echoStr = $_GET["echostr"];
            if ($return) {
                if ($this->checkSignature())
                    return $echoStr;
                else
                    return false;
            } else {
                if ($this->checkSignature())
                    die($echoStr);
                else
                    die('no access');
            }
        }

        if (!$this->checkSignature($encryptStr)) {
            if ($return)
                return false;
            else 
                die('no access');
        }
        return true;
    }
    
    /**
     * 设置发送消息
     * @param array $msg 消息数组
     * @param bool $append 是否在原消息数组追加
     */
    public function Message($msg = '',$append = false){
            if (is_null($msg)) {
                $this->_msg =array();
            }elseif (is_array($msg)) {
                if ($append)
                    $this->_msg = array_merge($this->_msg,$msg);
                else
                    $this->_msg = $msg;
                return $this->_msg;
            } else {
                return $this->_msg;
            }
    }
    
    public function setFuncFlag($flag) {
            $this->_funcflag = $flag;
            return $this;
    }
    
    private function log($log){
        if ($this->debug) {
            if (is_array($log))$log = print_r($log, true);
            if ( $this->_logcallback == 'reply' ){
                $this->text(htmlentities($log))->reply();
            } elseif (is_callable($this->_logcallback)) {
                call_user_func($this->_logcallback,$log);
            }
        }
    }
    
    /**
     * 获取微信服务器发来的信息
     * @param  string $debug_msg 调试的xml格式信息
     */
    public function getRev($debug_msg='')
    {
        if ($this->_receive) return $this;
        if($debug_msg){
            $postStr = $debug_msg;
        }else
            $postStr = !empty($this->postxml)?$this->postxml:file_get_contents("php://input");
        //兼顾使用明文又不想调用valid()方法的情况
        //$this->log($postStr);
        if (!empty($postStr)) {
            $this->_receive = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return $this;
    }
    
    /**
     * 获取微信服务器发来的信息
     */
    public function getRevData()
    {
        return $this->_receive;
    }
    
    /**
     * 获取消息发送者
     */
    public function getRevFrom() {
        if (isset($this->_receive['FromUserName']))
            return $this->_receive['FromUserName'];
        else 
            return false;
    }
    
    /**
     * 获取消息接受者
     */
    public function getRevTo() {
        if (isset($this->_receive['ToUserName']))
            return $this->_receive['ToUserName'];
        else 
            return false;
    }
    
    /**
     * 获取接收消息的类型
     */
    public function getRevType() {
        if (isset($this->_receive['MsgType']))
            return $this->_receive['MsgType'];
        else 
            return false;
    }
    
    /**
     * 获取消息ID
     */
    public function getRevID() {
        if (isset($this->_receive['MsgId']))
            return $this->_receive['MsgId'];
        else 
            return false;
    }
    
    /**
     * 获取消息发送时间
     */
    public function getRevCtime() {
        if (isset($this->_receive['CreateTime']))
            return $this->_receive['CreateTime'];
        else 
            return false;
    }
    
    /**
     * 获取接收消息内容正文
     */
    public function getRevContent(){
        if (isset($this->_receive['Content']))
            return $this->_receive['Content'];
        else if (isset($this->_receive['Recognition'])) //获取语音识别文字内容，需申请开通
            return $this->_receive['Recognition'];
        else
            return false;
    }
    
    /**
     * 获取接收消息图片
     */
    public function getRevPic(){
        if (isset($this->_receive['PicUrl']))
            return array(
                'mediaid'=>$this->_receive['MediaId'],
                'picurl'=>(string)$this->_receive['PicUrl'],    //防止picurl为空导致解析出错
            );
        else 
            return false;
    }
    
    /**
     * 获取接收消息链接
     */
    public function getRevLink(){
        if (isset($this->_receive['Url'])){
            return array(
                'url'=>$this->_receive['Url'],
                'title'=>$this->_receive['Title'],
                'description'=>$this->_receive['Description']
            );
        } else 
            return false;
    }
    
    /**
     * 获取接收地理位置
     */
    public function getRevGeo(){
        if (isset($this->_receive['Location_X'])){
            return array(
                'x'=>$this->_receive['Location_X'],
                'y'=>$this->_receive['Location_Y'],
                'scale'=>$this->_receive['Scale'],
                'label'=>$this->_receive['Label']
            );
        } else 
            return false;
    }
    
    /**
     * 获取上报地理位置事件
     */
    public function getRevEventGeo(){
            if (isset($this->_receive['Latitude'])){
                 return array(
                'x'=>$this->_receive['Latitude'],
                'y'=>$this->_receive['Longitude'],
                'precision'=>$this->_receive['Precision'],
            );
        } else
            return false;
    }
    
    /**
     * 获取接收事件推送
     */
    public function getRevEvent(){
        if (isset($this->_receive['Event'])){
            $array['event'] = $this->_receive['Event'];
        }
        if (isset($this->_receive['EventKey'])){
            $array['key'] = $this->_receive['EventKey'];
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        } else {
            return false;
        }
    }
    
    /**
     * 获取自定义菜单的扫码推事件信息
     * 
     * 事件类型为以下两种时则调用此方法有效
     * Event     事件类型，scancode_push
     * Event     事件类型，scancode_waitmsg
     * 
     * @return: array | false
     * array (
     *     'ScanType'=>'qrcode',
     *     'ScanResult'=>'123123'
     * )
     */
    public function getRevScanInfo(){
        if (isset($this->_receive['ScanCodeInfo'])){
            if (!is_array($this->_receive['SendPicsInfo'])) {
                $array=(array)$this->_receive['ScanCodeInfo'];
                $this->_receive['ScanCodeInfo']=$array;
            }else {
                $array=$this->_receive['ScanCodeInfo'];
            }
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        } else {
            return false;
        }
    }
    
    /**
     * 获取自定义菜单的图片发送事件信息
     * 
     * 事件类型为以下三种时则调用此方法有效
     * Event     事件类型，pic_sysphoto        弹出系统拍照发图的事件推送
     * Event     事件类型，pic_photo_or_album  弹出拍照或者相册发图的事件推送
     * Event     事件类型，pic_weixin          弹出微信相册发图器的事件推送
     * 
     * @return: array | false
     * array (
     *   'Count' => '2',
     *   'PicList' =>array (
     *         'item' =>array (
     *             0 =>array ('PicMd5Sum' => 'aaae42617cf2a14342d96005af53624c'),
     *             1 =>array ('PicMd5Sum' => '149bd39e296860a2adc2f1bb81616ff8'),
     *         ),
     *   ),
     * )
     * 
     */
    public function getRevSendPicsInfo(){
        if (isset($this->_receive['SendPicsInfo'])){
            if (!is_array($this->_receive['SendPicsInfo'])) {
                $array=(array)$this->_receive['SendPicsInfo'];
                if (isset($array['PicList'])){
                    $array['PicList']=(array)$array['PicList'];
                    $item=$array['PicList']['item'];
                    $array['PicList']['item']=array();
                    foreach ( $item as $key => $value ){
                        $array['PicList']['item'][$key]=(array)$value;
                    }
                }
                $this->_receive['SendPicsInfo']=$array;
            } else {
                $array=$this->_receive['SendPicsInfo'];
            }
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        } else {
            return false;
        }
    }

    /**
     * 获取自定义菜单的地理位置选择器事件推送
     *
     * 事件类型为以下时则可以调用此方法有效
     * Event     事件类型，location_select        弹出系统拍照发图的事件推送
     *
     * @return: array | false
     * array (
     *   'Location_X' => '33.731655000061',
     *   'Location_Y' => '113.29955200008047',
     *   'Scale' => '16',
     *   'Label' => '某某市某某区某某路',
     *   'Poiname' => '',
     * )
     * 
     */
    public function getRevSendGeoInfo(){
        if (isset($this->_receive['SendLocationInfo'])){
            if (!is_array($this->_receive['SendLocationInfo'])) {
                $array=(array)$this->_receive['SendLocationInfo'];
                if (empty($array['Poiname'])) {
                    $array['Poiname']="";
                }
                if (empty($array['Label'])) {
                    $array['Label']="";
                }
                $this->_receive['SendLocationInfo']=$array;
            } else {
                $array=$this->_receive['SendLocationInfo'];
            }
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        } else {
            return false;
        }
    }
    
    /**
     * 获取接收语音推送
     */
    public function getRevVoice(){
        if (isset($this->_receive['MediaId'])){
            return array(
                'mediaid'=>$this->_receive['MediaId'],
                'format'=>$this->_receive['Format'],
            );
        } else 
            return false;
    }
    
    /**
     * 获取接收视频推送
     */
    public function getRevVideo(){
        if (isset($this->_receive['MediaId'])){
            return array(
                    'mediaid'=>$this->_receive['MediaId'],
                    'thumbmediaid'=>$this->_receive['ThumbMediaId']
            );
        } else
            return false;
    }
    
    /**
     * 获取接收TICKET
     */
    public function getRevTicket(){
        if (isset($this->_receive['Ticket'])){
            return $this->_receive['Ticket'];
        } else
            return false;
    }
    
    /**
    * 获取二维码的场景值
    */
    public function getRevSceneId (){
        if (isset($this->_receive['EventKey'])){
            return str_replace('qrscene_','',$this->_receive['EventKey']);
        } else{
            return false;
        }
    }
    
    /**
    * 获取模板消息ID
    * 经过验证，这个和普通的消息MsgId不一样
    */
    public function getRevTplMsgID(){
        if (isset($this->_receive['MsgID'])){
            return $this->_receive['MsgID'];
        } else 
            return false;
    }
    
    /**
    * 获取模板消息发送状态
    */
    public function getRevStatus(){
        if (isset($this->_receive['Status'])){
            return $this->_receive['Status'];
        } else 
            return false;
    }
    
    public static function xmlSafeStr($str)
    {   
        return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';   
    } 
    
    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @return string
     */
    public static function data_to_xml($data) {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml    .=  "<$key>";
            $xml    .=  ( is_array($val) || is_object($val)) ? self::data_to_xml($val)  : self::xmlSafeStr($val);
            list($key, ) = explode(' ', $key);
            $xml    .=  "</$key>";
        }
        return $xml;
    }   
    
    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
    */
    public function xml_encode($data, $root='xml', $item='item', $attr='', $id='id', $encoding='utf-8') {
        if(is_array($attr)){
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr   = trim($attr);
        $attr   = empty($attr) ? '' : " {$attr}";
        $xml   = "<{$root}{$attr}>";
        $xml   .= self::data_to_xml($data, $item, $id);
        $xml   .= "</{$root}>";
        return $xml;
    }
    
    /**
     * 过滤文字回复\r\n换行符
     * @param string $text
     * @return string|mixed
     */
    private function _auto_text_filter($text) {
        if (!$this->_text_filter) return $text;
        return str_replace("\r\n", "\n", $text);
    }
    
    /**
     * 设置回复消息
     * Example: $obj->text('hello')->reply();
     * @param string $text
     */
    public function text($text='')
    {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_TEXT,
            'Content'=>$this->_auto_text_filter($text),
            'CreateTime'=>time(),
            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }
    
        /**
     * 设置回复消息
     * Example: $obj->image('media_id')->reply();
     * @param string $mediaid
     */
    public function image($mediaid='')
    {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_IMAGE,
            'Image'=>array('MediaId'=>$mediaid),
            'CreateTime'=>time(),
            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }
    
    /**
     * 设置回复消息
     * Example: $obj->voice('media_id')->reply();
     * @param string $mediaid
     */
    public function voice($mediaid='')
    {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_VOICE,
            'Voice'=>array('MediaId'=>$mediaid),
            'CreateTime'=>time(),
            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }
    
    /**
     * 设置回复消息
     * Example: $obj->video('media_id','title','description')->reply();
     * @param string $mediaid
     */
    public function video($mediaid='',$title='',$description='')
    {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_VIDEO,
            'Video'=>array(
                    'MediaId'=>$mediaid,
                    'Title'=>$title,
                    'Description'=>$description
            ),
            'CreateTime'=>time(),
            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }
    
    /**
     * 设置回复音乐
     * @param string $title
     * @param string $desc
     * @param string $musicurl
     * @param string $hgmusicurl
     */
    public function music($arr=array()) {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $result = $this->uploadMedia($arr['thumb_url'],'thumb');
        if (!$result) {
            return false;
        }
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'CreateTime'=>time(),
            'MsgType'=>self::MSGTYPE_MUSIC,
            'Music'=>array(
                'Title'=>$arr['title'],
                'Description'=>$arr['digest'],
                'MusicUrl'=>$arr['music_link'],
                'HQMusicUrl'=>$arr['music_high_link'],
                'ThumbMediaId' => $result['thumb_media_id']
            ),
            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }
    
    /**
     * 设置回复图文
     * @param array $newsData 
     * 数组结构:
     *  array(
     *      "0"=>array(
     *          'Title'=>'msg title',
     *          'Description'=>'summary text',
     *          'PicUrl'=>'http://www.domain.com/1.jpg',
     *          'Url'=>'http://www.domain.com/1.html'
     *      ),
     *      "1"=>....
     *  )
     */
    public function news($newsData=array())
    {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $count = count($newsData);
        
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_NEWS,
            'CreateTime'=>time(),
            'ArticleCount'=>$count,
            'Articles'=>$newsData,
            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }
    
    /**
     * 
     * 回复微信服务器, 此函数支持链式操作
     * Example: $this->text('msg tips')->reply();
     * @param string $msg 要发送的信息, 默认取$this->_msg
     * @param bool $return 是否返回信息而不抛出到浏览器 默认:否
     */
    public function reply($msg=array(),$return = false)
    {
        if (empty($msg)) 
            $msg = $this->_msg;
        $xmldata=  $this->xml_encode($msg);
        //$this->log($xmldata);
        if ($this->encrypt_type == 'aes') { //如果来源消息为加密方式
            $pc = new Prpcrypt($this->encodingAesKey);
            $array = $pc->encrypt($xmldata, $this->appid);
            $ret = $array[0];
            if ($ret != 0) {
                //$this->log('encrypt err!');
                return false;
            }
            $timestamp = time();
            $nonce = rand(77,999)*rand(605,888)*rand(11,99);
            $encrypt = $array[1];
            $tmpArr = array($this->token, $timestamp, $nonce,$encrypt);//比普通公众平台多了一个加密的密文
            sort($tmpArr, SORT_STRING);
            $signature = implode($tmpArr);
            $signature = sha1($signature);
            $xmldata = $this->generate($encrypt, $signature, $timestamp, $nonce);
            //$this->log($xmldata);
        }
        if ($return)
            return $xmldata;
        else
            echo $xmldata;
    }

    /**
     * xml格式加密，仅请求为加密方式时再用
     */
    private function generate($encrypt, $signature, $timestamp, $nonce)
    {
        //格式化加密信息
        $format = "<xml><Encrypt><![CDATA[%s]]></Encrypt><MsgSignature><![CDATA[%s]]></MsgSignature><TimeStamp>%s</TimeStamp><Nonce><![CDATA[%s]]></Nonce></xml>";
        return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
    }
    
    /**
     * GET 请求
     * @param string $url
     */
    private function http_get($url){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
    
    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    private function http_post($url,$param,$post_file=false){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
    
    /**
     * 通用auth验证方法，暂时仅用于菜单更新操作
     * @param string $appid
     * @param string $appsecret
     * @param string $token 手动指定access_token，非必要情况不建议用
     */
    public function checkAuth($appid='',$appsecret='',$token=''){
        if (!$appid || !$appsecret) {
            $appid = $this->appid;
            $appsecret = $this->appsecret;
        }
        if ($token) { 
            //手动指定token，优先使用
            $this->access_token=$token;
            return $this->access_token;
        }
        if(Cache::wechatToken($appid)){
            $this->access_token = Cache::wechatToken($appid);
            API_DEBUG && Yii::log('获取授权(缓存):appid='.$appid.'|appsecret='.$appsecret.'|token'.$this->token.'|access_token='.$this->access_token);
            return $this->access_token; 
        }
        $result = $this->http_get(self::API_URL_PREFIX.self::AUTH_URL.'appid='.$appid.'&secret='.$appsecret);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                return false;
            }
            $this->access_token = $json['access_token'];
            $expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 3600;
            Cache::wechatToken($appid,$this->access_token,$expire);
            API_DEBUG && Yii::log('获取授权(接口):appid='.$appid.'|appsecret='.$appsecret.'|token'.$this->token.'|access_token='.$this->access_token);
            return $this->access_token;
        }
        return false;
    }

    /**
     * 删除验证数据
     * @param string $appid
     */
    public function resetAuth($appid=''){
        if (!$appid) $appid = $this->appid;
        $this->access_token = '';
        //Cache::wechatToken($appid,null);
        return true;
    }

    /**
     * 微信api不支持中文转义的json结构
     * @param array $arr
     */
    static function json_encode($arr) {
        $parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) { //Custom handling for arrays
                if ($is_list)
                    $parts [] = self::json_encode ( $value ); /* :RECURSION: */
                else
                    $parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
            } else {
                $str = '';
                if (! $is_list)
                    $str = '"' . $key . '":';
                //Custom handling for multiple data types
                if (is_numeric ( $value ) && $value<2000000000)
                    $str .= $value; //Numbers
                elseif ($value === false)
                $str .= 'false'; //The booleans
                elseif ($value === true)
                $str .= 'true';
                else
                    $str .= '"' . addslashes ( $value ) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
        return '{' . $json . '}'; //Return associative JSON
    }

    /**
     * 创建菜单
     * @param array $data 菜单数组数据
     * example:
     *  array (
     *      'button' => array (
     *        0 => array (
     *          'name' => '扫码',
     *          'sub_button' => array (
     *              0 => array (
     *                'type' => 'scancode_waitmsg',
     *                'name' => '扫码带提示',
     *                'key' => 'rselfmenu_0_0',
     *              ),
     *              1 => array (
     *                'type' => 'scancode_push',
     *                'name' => '扫码推事件',
     *                'key' => 'rselfmenu_0_1',
     *              ),
     *          ),
     *        ),
     *        1 => array (
     *          'name' => '发图',
     *          'sub_button' => array (
     *              0 => array (
     *                'type' => 'pic_sysphoto',
     *                'name' => '系统拍照发图',
     *                'key' => 'rselfmenu_1_0',
     *              ),
     *              1 => array (
     *                'type' => 'pic_photo_or_album',
     *                'name' => '拍照或者相册发图',
     *                'key' => 'rselfmenu_1_1',
     *              )
     *          ),
     *        ),
     *        2 => array (
     *          'type' => 'location_select',
     *          'name' => '发送位置',
     *          'key' => 'rselfmenu_2_0'
     *        ),
     *      ),
     *  )
     * type可以选择为以下几种，其中5-8除了收到菜单事件以外，还会单独收到对应类型的信息。
     * 1、click：点击推事件
     * 2、view：跳转URL
     * 3、scancode_push：扫码推事件
     * 4、scancode_waitmsg：扫码推事件且弹出“消息接收中”提示框
     * 5、pic_sysphoto：弹出系统拍照发图
     * 6、pic_photo_or_album：弹出拍照或者相册发图
     * 7、pic_weixin：弹出微信相册发图器
     * 8、location_select：弹出地理位置选择器
     */
    public function createMenu($data){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_post(self::API_URL_PREFIX.self::MENU_CREATE_URL.'access_token='.$this->access_token,$data);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return true;
        }
        return false;
    }
    
    /**
     * 获取菜单
     * @return array('menu'=>array(....s))
     */
    public function getMenu(){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::MENU_GET_URL.'access_token='.$this->access_token);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 删除菜单
     * @return boolean
     */
    public function deleteMenu(){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::MENU_DELETE_URL.'access_token='.$this->access_token);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * 上传多媒体文件
     * 注意：数组的键值任意，但文件名前必须加@，使用单引号以避免本地路径斜杠被转义
     * @param array $data {"media":'@Path\filename.jpg'}
     * @param type 类型：图片:image 语音:voice 视频:video 缩略图:thumb
     * @return boolean|array
     */
    public function uploadMedia($address, $type){
        if (!$this->access_token && !$this->checkAuth())
            return false;
        $filename = dirname(Yii::app()->BasePath).$address;
        $param = array("media"=>'@'.$filename);
        // 新的http_post
         $result = $this->http_post(self::UPLOAD_MEDIA_URL.self::MEDIA_UPLOAD.'access_token='.$this->access_token.'&type='.$type,$param,true);
        if ($result) {
            $json = json_decode($result, true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 根据媒体文件ID获取媒体文件
     * @param string $media_id 媒体文件id
     * @return raw data
     */
    public function getMedia($media_id){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::UPLOAD_MEDIA_URL.self::MEDIA_GET_URL.'access_token='.$this->access_token.'&media_id='.$media_id);
        if ($result)
        {
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return $result;
        }
        return false;
    }

    /**
     * 上传图文消息素材
     * @param array $data 消息结构{"articles":[{...}]}
     * @return boolean|array
     */
    public function uploadArticles($data){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $datas='{"articles":[';
        foreach($data as $key => $value){
            $datas = $datas . '{';
            $datas = $datas . "\"thumb_media_id\":"."\"".$value['thumb_media_id']."\",";
            $datas = $datas . "\"author\":"."\"".$value['author']."\",";
            $datas = $datas . "\"title\":"."\"".$value['title']."\",";
            $datas = $datas . "\"content_source_url\":"."\"".$value['content_source_url']."\",";
            $datas = $datas . "\"content\":"."\"".$value['content']."\",";
            $datas = $datas . "\"digest\":"."\"".$value['digest']."\",";
            $datas = $datas . "\"show_cover_pic\":"."\"".$value['show_cover_pic']."\"";
            $datas = $datas . '},';
        }
        $datas = trim($datas,',');
        $datas = $datas . ']}';
        $result = $this->http_post(self::API_URL_PREFIX.self::MEDIA_UPLOADNEWS_URL.'access_token='.$this->access_token,$datas);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 实现分组群发消息的数据格式 2014/11/20
     $mediaId 当为文本类型时，变量内容为文本内容；
     $type:mpnews,text,voice,image,mpvideo
     */
    public function createGroupMassData($groupId,$type,$mediaId) {
        $data = '{';
        $data = $data . "\"filter\":{";
        $data = $data . "\"group_id\":".$groupId."},";
        $data = $data . "\"".$type."\":{";
        if($type == 'text'){
            $data = $data . "\"content\":\"".$mediaId."\"},";
        }
        else{
            $data = $data . "\"media_id\":\"".$mediaId."\"},";
        }
        $data = $data ."\"msgtype\":\"".$type."\"";
        $data = $data ."}";
        return $data;
    }
    
    /**
     * 实现分组群发消息的数据格式 2014/11/20
     $mediaId 当为文本类型时，变量内容为文本内容；
     $type:mpnews,text,voice,image,mpvideo 暂未兼容视频格式
     */
    public function createOpenIDMassData($openIds,$type,$mediaId) {
        $data = '{';
        $data = $data . "\"touser\":[\"".$openIds."\"],";
        $data = $data . "\"".$type."\":{";
        if($type == 'text'){
            $data = $data . "\"content\":\"".$mediaId."\"},";
        }
        else{
            $data = $data . "\"media_id\":\"".$mediaId."\"},";
        }
        $data = $data ."\"msgtype\":\"".$type."\"";
        $data = $data ."}";
        return $data;
    }
    
    /**
     * 高级群发消息, 根据OpenID列表群发图文消息
     * @param array $data 消息结构{ "touser":[ "OPENID1", "OPENID2" ], "mpnews":{ "media_id":"123dsdajkasd231jhksad" }, "msgtype":"mpnews" }
     * @return boolean|array
     */
    public function sendMassMessage($openIds,$type,$mediaId){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $datas = $this->createOpenIDMassData($openIds,$type,$mediaId);
        $result = $this->http_post(self::API_URL_PREFIX.self::MASS_SEND_URL.'access_token='.$this->access_token,$datas);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 高级群发消息, 根据群组id群发图文消息
     * @param array $data 消息结构{ "filter":[ "group_id": "2" ], "mpnews":{ "media_id":"123dsdajkasd231jhksad" }, "msgtype":"mpnews" }
     * @return boolean|array
     */
    public function sendGroupMassMessage($groupId,$type,$mediaId){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $datas = $this->createGroupMassData($groupId,$type,$mediaId);
        $result = $this->http_post(self::API_URL_PREFIX.self::MASS_SEND_GROUP_URL.'access_token='.$this->access_token,$datas);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 高级群发消息, 删除群发图文消息
     * @param int $msg_id 消息id
     * @return boolean|array
     */
    public function deleteMassMessage($msg_id){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_post(self::API_URL_PREFIX.self::MASS_DELETE_URL.'access_token='.$this->access_token,self::json_encode(array('msg_id'=>$msg_id)));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 创建二维码ticket
     * @param int $scene_id 自定义追踪id
     * @param int $type 0:临时二维码；1:永久二维码(此时expire参数无效)
     * @param int $expire 临时二维码有效期，最大为1800秒
     * @return array('ticket'=>'qrcode字串','expire_seconds'=>1800,'url'=>'二维码图片解析后的地址')
     */
    public function getQRCode($scene_id,$type=0,$expire=1800){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
            'action_name'=>$type?"QR_LIMIT_SCENE":"QR_SCENE",
            'expire_seconds'=>$expire,
            'action_info'=>array('scene'=>array('scene_id'=>$scene_id))
        );
        //if ($type == 1) {
        //   unset($data['expire_seconds']);
        //}
        $result = $this->http_post(self::API_URL_PREFIX.self::QRCODE_CREATE_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 获取二维码图片
     * @param string $ticket 传入由getQRCode方法生成的ticket参数
     * @return string url 返回http地址
     */
    public function getQRUrl($ticket) {
        return self::QRCODE_IMG_URL.urlencode($ticket);
    }
    
    /**
     * 长链接转短链接接口
     * @param string $long_url 传入要转换的长url
     * @return boolean|string url 成功则返回转换后的短url
     */
    public function getShortUrl($long_url){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
            'action'=>'long2short',
            'long_url'=>$long_url
        );
        $result = $this->http_post(self::API_URL_PREFIX.self::SHORT_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return $json['short_url'];
        }
        return false;
    }

    /**
     * 批量获取关注用户列表
     * @param unknown $next_openid
     */
    public function getUserList($next_openid=''){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::USER_GET_URL.'access_token='.$this->access_token.'&next_openid='.$next_openid);
        if ($result)
        {
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            //return $json;
            return $result;
        }
        return false;
    }
    
    /**
     * 获取关注者详细信息
     * @param string $openid
     * @return array {subscribe,openid,nickname,sex,city,province,country,language,headimgurl,subscribe_time,[unionid]}
     * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
    public function getUserInfo($openid){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::USER_INFO_URL.'access_token='.$this->access_token.'&openid='.$openid);
        if ($result)
        {
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            // return $json;
            return $result;
        }
        return false;
    }

    /**
     * 设置用户备注名
     * @param string $openid
     * @param string $remark 备注名
     * @return boolean|array
     */
    public function updateUserRemark($openid,$remark){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
            'openid'=>$openid,
            'remark'=>$remark
        );
        $result = $this->http_post(self::API_URL_PREFIX.self::USER_UPDATEREMARK_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 获取用户分组列表
     * @return boolean|array
     */
    public function getGroup(){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::GROUP_GET_URL.'access_token='.$this->access_token);
        if ($result)
        {
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 获取用户所在分组
     * @param string $openid
     * @return boolean|int 成功则返回用户分组id
     */
    public function getUserGroup($openid){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
                'openid'=>$openid
        );
        $result = $this->http_post(self::API_URL_PREFIX.self::USER_GROUP_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            } else 
                if (isset($json['groupid']))
                // return $json['groupid'];
                return $json;
        }
        return false;
    }
    
    /**
     * 新增自定分组
     * @param string $name 分组名称
     * @return boolean|array
     */
    public function createGroup($name){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
                'group'=>array('name'=>$name)
        );
        $result = $this->http_post(self::API_URL_PREFIX.self::GROUP_CREATE_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 更改分组名称
     * @param int $groupid 分组id
     * @param string $name 分组名称
     * @return boolean|array
     */
    public function updateGroup($groupid,$name){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
                'group'=>array('id'=>$groupid,'name'=>$name)
        );
        $result = $this->http_post(self::API_URL_PREFIX.self::GROUP_UPDATE_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 移动用户分组
     * @param int $groupid 分组id
     * @param string $openid 用户openid
     * @return boolean|array
     */
    public function updateGroupMembers($groupid,$openid){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
                'openid'=>$openid,
                'to_groupid'=>$groupid
        );
        $result = $this->http_post(self::API_URL_PREFIX.self::GROUP_MEMBER_UPDATE_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 发送客服消息
     * @param array $data 消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
     * @return boolean|array
     */
    public function sendCustomMessage($data=array(),$return = false){
        if (!$this->access_token && !$this->checkAuth()) return false;
        if(empty($data))
            $data=$this->_msg;
        $jsondata = $this->json_encode($data);
        if ( $this->_logcallback != 'custom' ) //$this->log($jsondata);
        if($return) 
            return $jsondata;
        $result = $this->http_post(self::API_URL_PREFIX.self::CUSTOM_SEND_URL.'access_token='.$this->access_token,$jsondata);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                $this->resetAuth($this->appid);
                return false;
            }
            // return $json;
                return true;
        }
        return false;
    }
    
    /**
     * oauth 授权跳转接口
     * @param string $callback 回调URI
     * @return string
     */
    public function getOauthRedirect($callback,$state='',$scope='snsapi_userinfo'){
        return self::OAUTH_PREFIX.self::OAUTH_AUTHORIZE_URL.'appid='.$this->appid.'&redirect_uri='.urlencode($callback).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
    }
    
    /**
     * 通过code获取Access Token
     * @return array {access_token,expires_in,refresh_token,openid,scope}
     */
    public function getOauthAccessToken(){
        $code = isset($_GET['code'])?$_GET['code']:'';
        if (!$code) return false;
        $result = $this->http_get(self::OAUTH_TOKEN_PREFIX.self::OAUTH_TOKEN_URL.'appid='.$this->appid.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code');
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->user_token = $json['access_token'];
            return $json;
        }
        return false;
    }
    
    /**
     * 刷新access token并续期
     * @param string $refresh_token
     * @return boolean|mixed
     */
    public function getOauthRefreshToken($refresh_token){
        $result = $this->http_get(self::OAUTH_TOKEN_PREFIX.self::OAUTH_REFRESH_URL.'appid='.$this->appid.'&grant_type=refresh_token&refresh_token='.$refresh_token);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->user_token = $json['access_token'];
            return $json;
        }
        return false;
    }
    
    /**
     * 获取授权后的用户资料
     * @param string $access_token
     * @param string $openid
     * @return array {openid,nickname,sex,province,city,country,headimgurl,privilege,[unionid]}
     * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
    public function getOauthUserinfo($access_token,$openid){
        $result = $this->http_get(self::OAUTH_USERINFO_URL.'access_token='.$access_token.'&openid='.$openid);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 检验授权凭证是否有效
     * @param string $access_token
     * @param string $openid
     * @return boolean 是否有效
     */
    public function getOauthAuth($access_token,$openid){
        $result = $this->http_get(self::OAUTH_AUTH_URL.'access_token='.$access_token.'&openid='.$openid);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            } else
              if ($json['errcode']==0) return true;
        }
        return false;
    }
    
    /**
     * 获取签名
     * @param array $arrdata 签名数组
     * @param string $method 签名方法
     * @return boolean|string 签名值
     */
    public function getSignature($arrdata,$method="sha1") {
        if (!function_exists($method)) return false;
        ksort($arrdata);
        $paramstring = "";
        foreach($arrdata as $key => $value)
        {
            if(strlen($paramstring) == 0)
                $paramstring .= $key . "=" . $value;
            else
                $paramstring .= "&" . $key . "=" . $value;
        }
        $paySign = $method($paramstring);
        return $paySign;
    }
    
    /**
     * 生成随机字串
     * @param number $length 长度，默认为16，最长为32字节
     * @return string
     */
    public function generateNonceStr($length=16){
        // 密码字符集，可任意添加你需要的字符
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for($i = 0; $i < $length; $i++)
        {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }
    /**
     * 发送模板消息
     * @param array $data 消息结构
     * ｛
     *       "touser":"OPENID",
     *       "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
     *       "url":"http://weixin.qq.com/download",
     *       "topcolor":"#FF0000",
     *       "data":{
     *           "参数名1": {
     *               "value":"参数",
     *               "color":"#173177"    //参数颜色
     *               },
     *           "Date":{
     *               "value":"06月07日 19时24分",
     *               "color":"#173177"
     *               },
     *           "CardNumber":{
     *               "value":"0426",
     *               "color":"#173177"
     *               },
     *           "Type":{
     *               "value":"消费",
     *               "color":"#173177"
     *               }
     *       }
     *   }
     * @return boolean|array
     */
    public function sendTemplateMessage($data){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_post(self::API_URL_PREFIX.self::TEMPLATE_SEND_URL.'access_token='.$this->access_token,self::json_encode($data));
        if($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 获取多客服会话记录
     * @param array $data 数据结构{"starttime":123456789,"endtime":987654321,"openid":"OPENID","pagesize":10,"pageindex":1,}
     * @return boolean|array
     */
    public function getCustomServiceMessage($data=array()){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
                'starttime' => $data['starttime'],
                'endtime' => $data['endtime'],
                'openid' => isset($data['openid'])?$data['openid']:'',
                'pagesize' => isset($data['pagesize'])?$data['pagesize']:10,
                'pageindex' => isset($data['pageindex'])?$data['pageindex']:1
            );
        $result = $this->http_post(self::API_URL_PREFIX.self::CUSTOM_SERVICE_GET_RECORD.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 转发多客服消息
     * Example: $obj->transfer_customer_service($customer_account)->reply();
     * @param string $customer_account 转发到指定客服帐号：test1@test
     */
    public function transfer_customer_service($customer_account = '')
    {
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'CreateTime'=>time(),
            'MsgType'=>'transfer_customer_service',
        );
        if (!$customer_account) {
            $msg['TransInfo'] = array('KfAccount'=>$customer_account);
        }
        $this->Message($msg);
        return $this;
    }
    
    /**
     * 获取多客服客服基本信息
     * 
     * @return boolean|array
     */
    public function getCustomServiceKFlist(){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::CUSTOM_SERVICE_GET_KFLIST.'access_token='.$this->access_token);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 获取多客服在线客服接待信息
     * 
     * @return boolean|array {
     *"kf_online_list": [
     *{
     *"kf_account": "test1@test",    //客服账号@微信别名
     *"status": 1,           //客服在线状态 1：pc在线，2：手机在线,若pc和手机同时在线则为 1+2=3
     *"kf_id": "1001",       //客服工号
     *"auto_accept": 0,      //客服设置的最大自动接入数
     *"accepted_case": 1     //客服当前正在接待的会话数
     *}
     *]
     *}
     */
    public function getCustomServiceOnlineKFlist(){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::CUSTOM_SERVICE_GET_ONLINEKFLIST.'access_token='.$this->access_token);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = ErrCode::getErrText($json['errcode']);
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 语义理解接口
     * @param String $uid      用户唯一id（非开发者id），用户区分公众号下的不同用户（建议填入用户openid）
     * @param String $query    输入文本串
     * @param String $category 需要使用的服务类型，多个用“，”隔开，不能为空
     * @param Float $latitude  纬度坐标，与经度同时传入；与城市二选一传入
     * @param Float $longitude 经度坐标，与纬度同时传入；与城市二选一传入
     * @param String $city     城市名称，与经纬度二选一传入
     * @param String $region   区域名称，在城市存在的情况下可省略；与经纬度二选一传入
     * @return boolean|array
     */
    public function querySemantic($uid,$query,$category,$latitude=0,$longitude=0,$city="",$region=""){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data=array(
                'query' => $query,
                'category' => $category,
                'appid' => $this->appid,
                'uid' => ''
        );
        //地理坐标或城市名称二选一
        if ($latitude) {
            $data['latitude'] = $latitude;
            $data['longitude'] = $longitude;
        } elseif ($city) {
            $data['city'] = $city;
        } elseif ($region) {
            $data['region'] = $region;
        }
        $result = $this->http_post(self::SEMANTIC_API_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
}



/**
 * PKCS7Encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder
{
    public static $block_size = 32;

    /**
     * 对需要加密的明文进行填充补位
     * @param $text 需要进行填充补位操作的明文
     * @return 补齐明文字符串
     */
    function encode($text)
    {
        $block_size = PKCS7Encoder::$block_size;
        $text_length = strlen($text);
        //计算需要填充的位数
        $amount_to_pad = PKCS7Encoder::$block_size - ($text_length % PKCS7Encoder::$block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = PKCS7Encoder::block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return 删除填充补位后的明文
     */
    function decode($text)
    {

        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > PKCS7Encoder::$block_size) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }

}

/**
 * Prpcrypt class
 *
 * 提供接收和推送给公众平台消息的加解密接口.
 */
class Prpcrypt
{
    public $key;

    function Prpcrypt($k)
    {
        $this->key = base64_decode($k . "=");
    }

    /**
     * 对明文进行加密
     * @param string $text 需要加密的明文
     * @return string 加密后的密文
     */
    public function encrypt($text, $appid)
    {

        try {
            //获得16位随机字符串，填充到明文之前
            $random = $this->getRandomStr();//"aaaabbbbccccdddd"; 
            $text = $random . pack("N", strlen($text)) . $text . $appid;
            // 网络字节序
            $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            $iv = substr($this->key, 0, 16);
            //使用自定义的填充方式对明文进行补位填充
            $pkc_encoder = new PKCS7Encoder;
            $text = $pkc_encoder->encode($text);
            mcrypt_generic_init($module, $this->key, $iv);
            //加密
            $encrypted = mcrypt_generic($module, $text);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);

            //          print(base64_encode($encrypted));
            //使用BASE64对加密后的字符串进行编码
            return array(ErrorCode::$OK, base64_encode($encrypted));
        } catch (Exception $e) {
            //print $e;
            return array( ErrorCode::$EncryptAESError, null);
        }
    }

    /**
     * 对密文进行解密
     * @param string $encrypted 需要解密的密文
     * @return string 解密得到的明文
     */
    public function decrypt($encrypted, $appid)
    {

        try {
            //使用BASE64对需要解密的字符串进行解码
            $ciphertext_dec = base64_decode($encrypted);
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            $iv = substr($this->key, 0, 16);
            mcrypt_generic_init($module, $this->key, $iv);
            //解密
            $decrypted = mdecrypt_generic($module, $ciphertext_dec);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (Exception $e) {
            return array(ErrorCode::$DecryptAESError, null);
        }


        try {
            //去除补位字符
            $pkc_encoder = new PKCS7Encoder;
            $result = $pkc_encoder->decode($decrypted);
            //去除16位随机字符串,网络字节序和AppId
            if (strlen($result) < 16)
                return "";
            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_appid = substr($content, $xml_len + 4);
            if (!$appid)
                $appid = $from_appid;
            //如果传入的appid是空的，则认为是订阅号，使用数据中提取出来的appid
        } catch (Exception $e) {
            //print $e;
            return array(ErrorCode::$IllegalBuffer, null);
        }
        if ($from_appid != $appid)
            return array(ErrorCode::$ValidateAppidError, null);
        //不注释上边两行，避免传入appid是错误的情况
        return array(0, $xml_content, $from_appid); //增加appid，为了解决后面加密回复消息的时候没有appid的订阅号会无法回复

    }

    /**
     * 随机生成16位字符串
     * @return string 生成的字符串
     */
    function getRandomStr()
    {

        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

}

/**
 * error code
 * 仅用作类内部使用，不用于官方API接口的errCode码
 */
class ErrorCode
{
    public static $OK = 0;
    public static $ValidateSignatureError = 40001;
    public static $ParseXmlError = 40002;
    public static $ComputeSignatureError = 40003;
    public static $IllegalAesKey = 40004;
    public static $ValidateAppidError = 40005;
    public static $EncryptAESError = 40006;
    public static $DecryptAESError = 40007;
    public static $IllegalBuffer = 40008;
    public static $EncodeBase64Error = 40009;
    public static $DecodeBase64Error = 40010;
    public static $GenReturnXmlError = 40011;
    public static $errCode=array(
            '0' => '处理成功',
            '40001' => '校验签名失败',
            '40002' => '解析xml失败',
            '40003' => '计算签名失败',
            '40004' => '不合法的AESKey',
            '40005' => '校验AppID失败',
            '40006' => 'AES加密失败',
            '40007' => 'AES解密失败',
            '40008' => '公众平台发送的xml不合法',
            '40009' => 'Base64编码失败',
            '40010' => 'Base64解码失败',
            '40011' => '公众帐号生成回包xml失败'
    );
    public static function getErrText($err) {
        if (isset(self::$errCode[$err])) {
            return self::$errCode[$err];
        }else {
            return false;
        };
    }
}

/**
 *  微信公众平台全局返回码类
 *  
 **/
class ErrCode
{
    public static $errCode=array(
        '-1'=>'系统繁忙',
        '0'=>'请求成功',
        '40001'=>'获取access_token时AppSecret错误，或者access_token无效',
        '40002'=>'不合法的凭证类型',
        '40003'=>'不合法的OpenID',
        '40004'=>'不合法的媒体文件类型',
        '40005'=>'不合法的文件类型',
        '40006'=>'不合法的文件大小',
        '40007'=>'不合法的媒体文件id',
        '40008'=>'不合法的消息类型',
        '40009'=>'不合法的图片文件大小',
        '40010'=>'不合法的语音文件大小',
        '40011'=>'不合法的视频文件大小',
        '40012'=>'不合法的缩略图文件大小',
        '40013'=>'不合法的APPID',
        '40014'=>'不合法的access_token',
        '40015'=>'不合法的菜单类型',
        '40016'=>'不合法的按钮个数',
        '40017'=>'不合法的按钮类型',
        '40018'=>'不合法的按钮名字长度',
        '40019'=>'不合法的按钮KEY长度',
        '40020'=>'不合法的按钮URL长度',
        '40021'=>'不合法的菜单版本号',
        '40022'=>'不合法的子菜单级数',
        '40023'=>'不合法的子菜单按钮个数',
        '40024'=>'不合法的子菜单按钮类型',
        '40025'=>'不合法的子菜单按钮名字长度',
        '40026'=>'不合法的子菜单按钮KEY长度',
        '40027'=>'不合法的子菜单按钮URL长度',
        '40028'=>'不合法的自定义菜单使用用户',
        '40029'=>'不合法的oauth_code',
        '40030'=>'不合法的refresh_token',
        '40031'=>'不合法的openid列表',
        '40032'=>'不合法的openid列表长度',
        '40033'=>'不合法的请求字符，不能包含\uxxxx格式的字符',
        '40035'=>'不合法的参数',
        '40038'=>'不合法的请求格式',
        '40039'=>'不合法的URL长度',
        '40050'=>'不合法的分组id',
        '40051'=>'分组名字不合法',
        '41001'=>'缺少access_token参数',
        '41002'=>'缺少appid参数',
        '41003'=>'缺少refresh_token参数',
        '41004'=>'缺少secret参数',
        '41005'=>'缺少多媒体文件数据',
        '41006'=>'缺少media_id参数',
        '41007'=>'缺少子菜单数据',
        '41008'=>'缺少oauth code',
        '41009'=>'缺少openid',
        '42001'=>'access_token超时',
        '42002'=>'refresh_token超时',
        '42003'=>'oauth_code超时',
        '43001'=>'需要GET请求',
        '43002'=>'需要POST请求',
        '43003'=>'需要HTTPS请求',
        '43004'=>'需要接收者关注',
        '43005'=>'需要好友关系',
        '44001'=>'多媒体文件为空',
        '44002'=>'POST的数据包为空',
        '44003'=>'图文消息内容为空',
        '44004'=>'文本消息内容为空',
        '45001'=>'多媒体文件大小超过限制',
        '45002'=>'消息内容超过限制',
        '45003'=>'标题字段超过限制',
        '45004'=>'描述字段超过限制',
        '45005'=>'链接字段超过限制',
        '45006'=>'图片链接字段超过限制',
        '45007'=>'语音播放时间超过限制',
        '45008'=>'图文消息超过限制',
        '45009'=>'接口调用超过限制',
        '45010'=>'创建菜单个数超过限制',
        '45015'=>'回复时间超过限制',
        '45016'=>'系统分组，不允许修改',
        '45017'=>'分组名字过长',
        '45018'=>'分组数量超过上限',
        '45024'=>'账号数量超过上限',
        '46001'=>'不存在媒体数据',
        '46002'=>'不存在的菜单版本',
        '46003'=>'不存在的菜单数据',
        '46004'=>'不存在的用户',
        '47001'=>'解析JSON/XML内容错误',
        '48001'=>'api功能未授权',
        '50001'=>'用户未授权该api',
        '7000000'=>'请求正常，无语义结果',
        '7000001'=>'缺失请求参数',
        '7000002'=>'signature 参数无效',
        '7000003'=>'地理位置相关配置 1 无效',
        '7000004'=>'地理位置相关配置 2 无效',
        '7000005'=>'请求地理位置信息失败',
        '7000006'=>'地理位置结果解析失败',
        '7000007'=>'内部初始化失败',
        '7000008'=>'非法 appid（获取密钥失败）',
        '7000009'=>'请求语义服务失败',
        '7000010'=>'非法 post 请求',
        '7000011'=>'post 请求 json 字段无效',
        '7000030'=>'查询 query 太短',
        '7000031'=>'查询 query 太长',
        '7000032'=>'城市、经纬度信息缺失',
        '7000033'=>'query 请求语义处理失败',
        '7000034'=>'获取天气信息失败',
        '7000035'=>'获取股票信息失败',
        '7000036'=>'utf8 编码转换失败',
    );
    
    public static function getErrText($err) {
        if (isset(self::$errCode[$err])) {
            return self::$errCode[$err];
        }else {
            return false;
        };
    }
}
?>