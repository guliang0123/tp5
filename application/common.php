<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 获取ip地址
 * @return string
 */
function getip()
{
    if(!empty($_SERVER["HTTP_CLIENT_IP"]))
        $cip = $_SERVER["HTTP_CLIENT_IP"];
    else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
        $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    else if(!empty($_SERVER["REMOTE_ADDR"]))
        $cip = $_SERVER["REMOTE_ADDR"];
    else
        $cip = "127.0.0.1";
    $cip = preg_match('/[\d\.]{7,15}/', $cip, $matches) ? $matches[0] : '';
    return $cip;
}
/**
 * 根据ip地址获取省市信息
 * @return string  '浙江杭州'
 */
function getAddrByIp($queryIP)
{
    $url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=' . $queryIP;
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_ENCODING, 'utf8');
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回
    $location = curl_exec($ch);
    $location = json_decode($location,true);
    curl_close($ch);
    $loc = "";
    if($location === FALSE)
        return "";
    if(!empty($location['province']) && !empty($location['city'])) {
        $loc = $location['province'] . $location['city'] ;
    } else {
        $loc = isset($location['desc']) ? $location['desc'] : '无法识别！';
    }
    return $loc;
}
/***
 * 生成随机字符串
 * @param int $length
 * @return string
 */
function getRandStr($length = 8, $UID)
{
    $code_arr = M('code')->where(['UID' => $UID])->find();
    if ($code_arr) {
        return $code_arr['Code'];
    }
    // 密码字符集，可任意添加你需要的字符
    $chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        // 这里提供两种字符获取方式
        // 第一种是使用 substr 截取$chars中的任意一位字符；
        // 第二种是取字符数组 $chars 的任意元素
        // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
        $password .= $chars[mt_rand(0, strlen($chars) - 1)];

    }
    $password  = THINK_START_TIME . $password;
    $C         = new \Home\Model\CodeModel();
    $c         = [];
    $c['UID']  = $UID;
    $c['Code'] = $password;
    $C->addedit($c);
    return $password;
}

/***
 * 写入数据日志
 *  msg 写入信息，数组将转换成json
 *
 */
function dblog($msg = '', $url = false)
{
    $data['url'] = $url ? $url : url();
    $data['msg'] = is_string($msg) ? $msg : json_encode($msg, JSON_UNESCAPED_UNICODE);

    db('log')->insert($data);
}

/**
 * 无限极分类
 *
 * @param        $classify_old 需分类数组
 * @param string $id 唯一id名称
 * @param string $pname 父id键名称
 * @param int $pid 父id，默认0为顶级父id
 *
 * @return array|bool
 */

define('NOW_TIME',$_SERVER['REQUEST_TIME']);
function toClass($classify_o, $id = 'id', $pname = 'pid', $pid = 0)
{
    $num             = 0;
    $classify        = [];
    $classify_o_copy = $classify_o;
    foreach ($classify_o as $k => $v) {
        if ($v[$pname] == $pid) {
            $num++;
            array_push($classify, $v);
            unset($classify_o_copy[$k]);
        }
    }
    if ($num == 0) {
        return false;
    }
    foreach ($classify as $k => $v) {
        $a = toClass($classify_o_copy, $id, $pname, $v[$id]);
        if (!$a) {
            continue;
        }
        $classify[$k]['children'] = $a;
    }
    return $classify;
}
//传入分类列表，处理出树形结构函数
function getTree($arr = array(),$upid=0,$index=0){
    $tree = array();
    foreach ($arr as $value) {

        if($value['upid']==$upid){
            $value['name'] = str_repeat('┣━', $index).$value['name'];
            $tree[] = $value;
            $tree = array_merge($tree,getTree($arr,$value['catid'],$index+1));
        }
    }
    return $tree;
}
/***
 * @param $phone
 * @param string $content
 * @param string $template
 * @return array
 * 发送短信
 */
function sendMessage($phone, $content, $template)
{

    $uid     = 'yiyunhao';//ftds
    $content = urlencode($content);

    $pwd = 'e76309e0c6ff4dd045589a2891a790e9';//e76309e0c6ff4dd045589a2891a790e9 ..2347a648f7d0d1329c5bd793a3c12384
    //&template=377804
    $url = "http://api.sms.cn/sms/?ac=send&uid={$uid}&pwd={$pwd}&mobile={$phone}&content={$content}&template={$template}";
    //echo $url;

    $r = doPost($url);

    return $r;
}

/* 生成验证密码 */
function makepwd($pwd, $key = '20160121sPenCeR_(>~axM8^OW5@6h{`0SyJ.*jLt#|dZ)4Bsf%=e}9R_')
{
    // return '' === $pwd ? '' : md5(sha1($pwd) . $key);
    return '' === $pwd ? '' : md5($pwd);
}

/***
 * GET提交
 */
function doGet($url)
{
    //初始化
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    //执行并获取HTML文档内容
    $output = curl_exec($ch);


    //释放curl句柄
    curl_close($ch);

    //打印获得的数据
    return $output;
}

/**
 * 获取IP地址
 */
function get_ip()
{
    return $_SERVER['REMOTE_ADDR'];
}

/***
 * 去除输入框中的前后空
 *
 * @param  array $data
 * return array
 */
function dislodge($data)
{
    foreach ($data as &$value) {
        if (!is_array($value)) {

            $value = trim($value);
        }
    }
    return $data;
}

function doPost($url, $body = [], $header = array(), $type = "POST")
{
    //1.创建一个curl资源
    $ch = curl_init();
    //2.设置URL和相应的选项
    curl_setopt($ch, CURLOPT_URL, $url);//设置url
    //1)设置请求头
    array_push($header, 'Accept:application/json');
    array_push($header, 'Accept-Charset:utf-8');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    //设置发起连接前的等待时间，如果设置为0，则无限等待。
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //2)设置提交方式
    switch ($type) {
        case "GET":
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            break;
        case "POST":
            curl_setopt($ch, CURLOPT_POST, true);
            break;
        case "PUT"://使用一个自定义的请求信息来代替"GET"或"HEAD"作为HTTP请求。这对于执行"DELETE" 或者其他更隐蔽的HTT
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            break;
        case "DELETE":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            break;

    }
    //3)设备请求体
    if (count($body) > 0) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));//全部数据使用HTTP协议中的"POST"操作来发送。
    }
    //设置请求头
    if (count($header) > 0) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    //4)"User-Agent: "头的字符串。
    curl_setopt($ch, CURLOPT_USERAGENT, 'SSTS Browser/1.0');
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)'); // 模拟用户使用的浏览器
    //5.抓取URL并把它传递给浏览器
    $res    = curl_exec($ch);
    $encode = mb_detect_encoding($res, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
    if ($encode != 'UTF-8') {
        $res = iconv('GBK', "UTF-8", $res);

    }

    //$res = mb_detect_encoding($res,'UTF-8',$encode);

    $result = json_decode($res, true);


    //4.关闭curl资源，并且释放系统资源
    curl_close($ch);
    if (empty($result))
        return $res;
    else
        return $result;


}

/* 生产验证码
$len 验证码的长度
*/
function randnum($len = 4)
{
    $num = '0123456789';
    $str = "";
    for ($i = 0; $i < $len; $i++) {
        $str .= substr($num, mt_rand(0, strlen($num) - 1), 1);
    }
    return $str;
}


/* phpmail发送邮件 */
function phpsendmail($mailcode, $email = '290847350@qq.com', $type = 0, $msg = false)
{
    vendor('PHPMailer.PHPMailerAutoload');
    $mail = new PHPMailer();
    // $mail->SMTPDebug = 3; // Enable verbose debug output

    $mail->isSMTP(); // Set mailer to use SMTP
    $mail->Host     = 'smtp.qq.com'; // Specify main and backup SMTP servers
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = '290847350@qq.com'; // SMTP username
    $mail->Password = 'iqsghrmbzjnicajj'; // SMTP password
    // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 25; // TCP port to connect to
    $mail->setFrom('290847350@qq.com', '教育之窗'); //
    $mail->addAddress($email, '尊敬的客户'); // 收件人邮箱 // Add a recipient

    $mail->isHTML(true); // Set email format to HTML

    $mail->Subject = '教育之窗'; // 邮箱标题

    $mail->Body
        = <<<tang
<style>
.mmsgLetter	{ width:580px;margin:0 auto;padding:10px;color:#333;background:#fff;border:0px solid #aaa;border:1px solid #aaa\9;border-radius:5px;-webkit-box-shadow:3px 3px 10px #999;-moz-box-shadow:3px 3px 10px #999;box-shadow:3px 3px 10px #999;font-family:Verdana, sans-serif; }
.mmsgLetter a:link,.mmsgLetter a:visited{color:#407700; }
.mmsgLetterContent {text-align:left;padding:30px;font-size:14px;line-height:1.5; }
.mmsgLetterContent h3{ color:#000;font-size:20px;font-weight:bold; margin:20px 0 20px;border-top:2px solid #eee;padding:20px 0 0 0;font-family:"微软雅黑","黑体", "Lucida Grande", Verdana, sans-serif;}
.mmsgLetterContent p{margin:20px 0;padding:0; }
.mmsgLetterContent .salutation { font-weight:bold;}
.mmsgLetterHeader{	height:23px; }
</style>
<div style="background-color:#d0d0d0;text-align:center;padding:40px;">
	<div style="width:580px;margin:0 auto;padding:10px;color:#333;background-color:#fff;border:0px solid #aaa;border-radius:5px;-webkit-box-shadow:3px 3px 10px #999;-moz-box-shadow:3px 3px 10px #999;box-shadow:3px 3px 10px #999;font-family:Verdana, sans-serif; " class="mmsgLetter">
		<div style="height:23px;" class="mmsgLetterHeader"></div>
		<div style="text-align:left;padding:30px;font-size:14px;line-height:1.5;" class="mmsgLetterContent">
			<div>
				<p style="font-weight:bold;" class="salutation">Hi,<span id="mailUserName">%s</span>：</p>
				<p>教育之窗正在发送%s验证码，邮件地址为<a href="mailto:%s" target="_blank">%s</a></p>
				<p>
					如果这是你的操作，请将此验证码(<a>%s</a>)输入到手机上完成邮箱注册<br>
					如果你没有操作注册此邮箱，请忽略此邮件。
				</p>
			</div>
		</div>
	</div>
</div>;//html内容
tang;
    $mail->Body = sprintf($mail->Body, $type, $email, $email, $email, $mailcode);
    if ($msg) {
        $mail->Body = '自动处理订单错误以下是错误的内容：请手工修改：' . $msg;

    }
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    if (!$mail->send()) {
        return 'Error:' . $mail->ErrorInfo;
    } else {
        return $mailcode;
    }
}

/* 阿里云oss上传文件 */
//include('/data/source/Alioos/alioss.php');

/**
 * 上传文件到oss并删除本地文件
 *
 * @param string $path
 *            文件路径
 *
 * @return bollear 是否上传
 */
function oss_upload_form($key = '')
{
    vendor('Oss/autoload');
    // 获取配置项
    if ($key) {
        $files = $_FILES[$key];
        //    $file = $_FILES[$key]['tmp_name'];
    } else {
        $files = current($_FILES);
    }


    $file   = $files['tmp_name'];
    $bucket = 'ziqiangkeji';

    if (file_exists($file)) {
        // $type = strstr($file, '.');
        $type      = strstr($files['name'], '.');
        $file_name = NOW_TIME . rand(1111, 9999) . $type;
        // 实例化oss类
        $config = array(
            'KEY_ID' => 'LTAIrrvJE9cEcu9l', // 阿里云oss key_id
            'KEY_SECRET' => 'zQDdb8Udmt9TKQAQpID49ueHY5rGgj', // 阿里云oss key_secret
            'END_POINT' => 'http://oss-cn-shanghai.aliyuncs.com', // 阿里云oss endpoint
            'BUCKET' => 'ziqiangkeji'// bucken 名称
        );
        $oss    = new \OSS\OssClient($config['KEY_ID'], $config['KEY_SECRET'], $config['END_POINT']);
        //        $oss = new_oss();

        try {
            $oss->uploadFile($bucket, 'images/' . $file_name, $file);

            unlink($file);
            $oss_path = 'http://ziqiangkeji.oss-cn-shanghai.aliyuncs.com/images/';//oss图片前缀
            return $oss_path . $file_name;
            // 上传成功，自己编码
            // 这里可以删除上传到本地的文件。unlink（$file）；
        } catch (OssException $e) {
            // 上传失败，自己编码
            printf($e->getMessage() . "\n");
            return 'error上传到阿里云服务器失败';
        }
    } else {
        return 'error本地文件不存在';
    }
}

/***
 * 上传oss文件
 * @param $file
 *
 * @return string
 */

function oss_upload_file($file)
{
    // 获取配置项
    $bucket = 'sbswz';

    if (file_exists($file)) {
        // $type = strstr($file, '.');
        $type      = strstr($file, '.');
        $file_name = NOW_TIME . rand(1111, 9999) . $type;
        // 实例化oss类
        $oss = new_oss();

        try {
            $oss->uploadFile($bucket, 'jy_img/' . $file_name, $file);

            unlink($file);
            return $file_name;
            // 上传成功，自己编码
            // 这里可以删除上传到本地的文件。unlink（$file）；
        } catch (OssException $e) {
            // 上传失败，自己编码
            printf($e->getMessage() . "\n");
            return 'error上传到阿里云服务器失败';
        }
    } else {
        return 'error本地文件不存在';
    }
}

/* 获取配置数据 */
function get_config($arr = array())
{
    $CONFIG = M('config')->where('is_del=0')->select();

    foreach ($CONFIG as $key => $value) {
        $arr[$value['key']] = $value['value'];
    }

    return $arr;
}

// 生成签名
function createSign($arr)
{
    if (key_exists('sign', $arr)) {
        unset($arr['sign']);
    }
    ksort($arr);
    $encrypt_str = '';
    foreach ($arr as $k => $v) {
        $encrypt_str .= $k . $v;
    }
    $encrypt_str = md5(C('sign_param') . md5($encrypt_str) . C('sign_param'));
    return $encrypt_str;
}

// 验证签名
function checkSign($arr)
{
    if (!key_exists('sign', $arr)) {
        return false;
    }
    $sign   = $arr['sign'];
    $sign_t = createSign($arr);
    if ($sign === $sign_t) {
        return true;
    }
    return false;
}

/***
 * 导出execl
 *
 * @param $header 头部数组A1=>value
 * @param $data   数组数据普通
 * @param $field  a=>字段名
 */
function outExecl($data = null, $header = '')
{

    $objPHPExcel = new PHPExcel();
    /*以下是一些设置 ，什么作者  标题啊之类的*/
    $objPHPExcel->getProperties()->setCreator("转弯的阳光")
        ->setLastModifiedBy("转弯的阳光")
        ->setTitle("数据EXCEL导出")
        ->setSubject("数据EXCEL导出")
        ->setDescription("备份数据")
        ->setKeywords("excel")
        ->setCategory("result file");
    /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/
    if ($header) {
        $k = 'A';
        foreach ($header as $key => $value) {
            $objPHPExcel->getActiveSheet()->getStyle($k . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->setActiveSheetIndex(0)
                //Excel的第A列，uid是你查出数组的键值，下面以此类推
                ->setCellValue($k . '1', $value);

            $k++;

        }
    }

    $num = isset($header) ? 1 : 0;
    foreach ($data as $key => $v) {
        $num++;
        $k = 'A';
        foreach ($v as $vv) {

            $objPHPExcel->setActiveSheetIndex(0)
                //Excel的第A列，uid是你查出数组的键值，下面以此类推
                ->setCellValue($k . $num, $vv)
                ->getColumnDimension($k)->setwidth(20);
            $objPHPExcel->getActiveSheet()->getStyle($k . $num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $k++;

        }
    }


    $objPHPExcel->getActiveSheet()->setTitle('小记者导出列表');
    //    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $name . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;


}


//excel导入数据方法
function excel_import($filename, $exts = 'xls')
{

    //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
    //    import("Common.PHPExcel.PHPExcel" , '' , '.php');
    //创建PHPExcel对象，注意，不能少了\
    $PHPExcel = new \PHPExcel();
    //如果excel文件后缀名为.xls，导入这个类
    if ($exts == 'xls') {
        //        import("Common.PHPExcel.PHPExcel.Reader.Excel5" , '' , '.php');
        $PHPReader = new \PHPExcel_Reader_Excel5();
    } else if ($exts == 'xlsx') {
        //        import("Common.PHPExcel.phpexcel.Reader.Excel2007" , '' , '.php');
        $PHPReader = new \PHPExcel_Reader_Excel2007();
    }
    if (!file_exists($filename)) {
        return ['errMsg' => '文件不存在！'];
    }

    //载入文件
    $PHPExcel = $PHPReader->load($filename);
    //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
    $currentSheet = $PHPExcel->getSheet(1);
    //获取总列数
    //    $allColumn = $currentSheet->getHighestColumn();
    $allColumn = 'N';
    //获取总行数
    $allRow = $currentSheet->getHighestRow();
    //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
    for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
        //从哪列开始，A表示第一列
        $temp = [];
        for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
            //数据坐标
            //            echo $allColumn;
            $address = $currentColumn . $currentRow;
            //读取到的数据，保存到数组$arr中
            $cell = $currentSheet->getCell($address)->getValue();

            //$cell = $data[$currentRow][$currentColumn];
            if ($cell instanceof PHPExcel_RichText) {
                $cell = $cell->__toString();
            }
            $temp[] = $cell;
        }
        $data[] = $temp;
    }

    return $data;
}


/***
 * 处理小数
 * @param $money
 * @return float|int
 */
function jisuanxiaoshu($money)
{
    $money = $money * 100;
    $money = intval($money);
    $money = floor($money / 100);
    return $money;
}

function getPirewhere($ID, $member)
{
    $arr    = [];
    $config = M('honorconfig')->where(['GoodsGrade' => 1])->find();

    // 获取直推人数
    $n = M('member')->where(['ReferenceMemberNumber' => $member])->count();
    //获取团队人数
    $map               = [];
    $map['Path']       = ['exp', ' regexp ' . '\'' . $ID . '\''];
    $map['GoodsGrade'] = ['gt', 0];
    $n1                = M('member')->where($map)->count();
    //    dblog($config);
    //    dblog($n1.'团队人数');
    //获取团队业绩
    $map                  = [];
    $map['me.Path']       = ['exp', ' regexp ' . '\'' . $ID . '\''];
    $map['me.GoodsGrade'] = ['gt', 0];
    $m1                   = M('member me')
        ->where($map)
        ->field(['g.Money'])
        ->join('stock_goodsgradeconfig g ON me.GoodsGrade=g.GoodsGrade')
        ->sum('g.Money');

    if ($n < $config['Person1']) {
        $arr['PushNumber'] = 1;

    } else {
        $arr['PushNumber'] = 0;
    }

    if ($n1 < $config['person2']) {
        $arr['TeamNumber'] = 1;
    } else {
        $arr['TeamNumber'] = 0;
    }
    if ($m1 < $config['Money1']) {
        $arr['TeamEarnings'] = 1;
    } else {
        $arr['TeamEarnings'] = 0;
    }
    return $arr;

}

/***
 * 获取星级信息文本
 * @param $Grade
 * @return string
 */
function getGrade($Grade)
{
    switch ($Grade) {
        case 0:
            $s = '未认购';
            break;

        case 1:
            $s = '✯合伙人';
            break;
        case 2:
            $s = '✯✯合伙人';
            break;
        case 3:
            $s = '✯✯✯合伙人';
            break;
        case 4:
            $s = '✯✯✯✯合伙人';
            break;
        case 5:
            $s = '✯✯✯✯✯合伙人';
            break;
        case 6:
            $s = '股东';
            break;
        default:
            $s = '未知合伙人';


    }
    return $s;
}

/***
 * 计算分销奖人数
 * @param $n 代数
 * @param $member 当前会员编号
 * @return bool
 */
function zhituirenshu($n, $member)
{
    $n1 = M('member')->where(['ReferenceMemberNumber' => $member, 'GoodsGrade' => ['gt', 0]])->count();
    switch ($n) {
        case 0:
            $r = 1;
            break;
        case 1:
            $r = 1;
            break;
        case 2:
            $r = $n1 >= 3 ? 1 : 0;
            break;
        case 3:
            $r = $n1 >= 7 ? 1 : 0;
            break;
        default:
            $r = 0;
    }
    return $r;
}

/***
 * 读取数据结构
 */
function ReadDb()
{
    //要查询的数据库
    $dbname = C('DB_NAME');

    $tables = M()->query('SELECT TABLE_NAME,TABLE_COMMENT FROM information_schema.TABLES WHERE table_schema=' . '\'' . $dbname . '\'');
    $txt    = '';
    foreach ($tables as $key => $value) {
        $txt .= '*```(' . $value['TABLE_NAME'] . ')  注释(' . $value['TABLE_COMMENT'] . ')' . "\r\n";

        $arr = M()->query('SELECT * FROM INFORMATION_SCHEMA.Columns WHERE table_name=' . '\'' . $value['TABLE_NAME'] . '\' AND table_schema=' . '\'' . $dbname . '\'');
        foreach ($arr as $v) {
            $txt .= '                                      ' . $v['COLUMN_NAME'] . '(' . $v['COLUMN_TYPE'] . ')      默认值:' . $v['COLUMN_DEFAULT'] . '   注释:' . $v['COLUMN_COMMENT'] . "\r\n";
        }
        // dump(M()->query(''));
        //$tablesmsg = M()->query('show columns from '.$value['Tables_in_zichan']);
        // dump($tablesmsg);
        $txt .= '*******************************************************************************' . "\r\n";
    }

    file_put_contents('./Updown/dbdate.txt', $txt);
    echo '数据库读取完成';
}

/*
将秒数转化为天/小时/分/秒
*/
function secondToStr($time){

    $str = '';
    $timearr = array(86400 => '天', 3600 => '小时', 60 => '分', 1 => '秒');
    foreach ($timearr as $key => $value) {
        if ($time >= $key)
            $str .= floor($time/$key) . $value;
        $time %= $key;
    }
    return $str;
}

/*
 * 将秒数转为/小时/分/秒
 */
function secondToHstr($time){
    $str = '';
    $timearr = array(3600 => '小时', 60 => '分', 1 => '秒');
    foreach ($timearr as $key => $value) {
        if ($time >= $key)
            $str .= floor($time/$key) . $value;
        $time %= $key;
    }
    return $str;
}

/**
 * curl 模拟post请求
 * @param $url
 * @param $data
 * @param bool $retJson
 * @param bool $setHeader
 * @return array|mixed|object
 */
function do_post($url, $data , $retJson = true ,$setHeader = false){
    $auth = Ebh::app()->getInput()->cookie('auth');
    $uri = Ebh::app()->getUri();
    $domain = $uri->uri_domain();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    if ($setHeader) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
    }
    if(!empty($_SERVER['HTTP_USER_AGENT'])){
        curl_setopt($ch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
    }
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_COOKIE, 'ebh_auth='.urlencode($auth).';ebh_domain='.$domain);
    $ret = curl_exec($ch);
    curl_close($ch);
    if($retJson == false){
        $ret = json_decode($ret);
    }
    return $ret;
}

//隐藏名字第二个字(中英文)
function hidename($name){
    $strlen = mb_strlen($name, 'utf-8');
    $firstStr = mb_substr($name, 0, 1, 'utf-8');
    $lastStr = mb_substr($name, 2, $strlen - 2, 'utf-8');
    $name = $firstStr.'*'.$lastStr;
    return $name;
}

/**
 * json格式输出
 * @param number $code 状态标识 0 成功 1 失败
 * @param string $msg 输出消息
 * @param array $data 数组参数数组
 * @param string $exit 是否结束退出
 */
if(!function_exists('renderjson')){
    function renderjson($code=0,$msg="",$data=array(),$exit=true){
        $arr = array(
            'code'=>(intval($code) ===0) ? 0 : intval($code),
            'msg'=>$msg,
            'data'=>$data
        );
        echo json_encode($arr,JSON_UNESCAPED_UNICODE);
        if($exit){
            exit();
        }
    }
}
/**
 * 程序调试输出
 * @param $cont
 * @param bool $isDie
 */
if(!function_exists('dump')){
    function dump($data,$isDie=true){
        $str = '<div style="clear: both"><pre>';
        $str .= print_r($data,true);//加true表示转为字符串输出
        $str .= '</div>';
        echo $str;
        if($isDie){
            exit();
        }
    }
}