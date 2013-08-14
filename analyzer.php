<?php
//This is the main file behind the working of the code
//Testing on heroku
function objectsIntoArray($arrObjData, $arrSkipIndices = array())
{
    $arrData = array();
    
    // if input is object, convert into array
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }
    
    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
    return $arrData;
}

$sitedata=array();
ob_start();
if(array_key_exists('csv', $_REQUEST)&&$_REQUEST['csv']=='true'){
	header('Content-type: text/html');
	header('Content-Disposition: attachment; filename="sitemapreport.csv"');
}
if(isset($_REQUEST['url'])&&$_REQUEST['url']!=''){
	ob_implicit_flush(true);
	$urls=explode("\r\n", $_REQUEST['url']);
	foreach($urls as $url){
		$xmlUrl = $url; // XML feed file/URL
		$xmlStr = file_get_contents($xmlUrl);
		$xmlObj = simplexml_load_string($xmlStr);
		$arrXml = objectsIntoArray($xmlObj);
		foreach($arrXml['url'] as $key=>$link){
			$arrXml['url'][$key]['response']=get_headers($link['loc']);
			if(stripos($arrXml['url'][$key]['response'][0],'301 Moved Permanently')!==FALSE||stripos($arrXml['url'][$key]['response'][0],'302 Found')!==FALSE){
				foreach($arrXml['url'][$key]['response'] as $item){
					if(stripos($item,'Location: ')!==FALSE)
						$arrXml['url'][$key]['redirection_location']=substr($item,stripos($item,'http'));
					echo '-';
					flush();
					ob_flush();
				}
				if(array_key_exists('redirection_location',$arrXml['url'][$key] )){

					$arrXml['url'][$key]['redirection']=get_headers($arrXml['url'][$key]['redirection_location']);
				}
			}
		}
		$arrXml['sitemap']=$url;
		$sitedata[]=$arrXml;
	}
	if(array_key_exists('csv', $_REQUEST)&&$_REQUEST['csv']=='true'){
		
		foreach($sitedata as $arrXml){
			echo 'Sitemap:'.$arrXml['sitemap']."\r\n";
			echo "\r\nId,Link,Status,Redirection,Redirection_Status\r\n";
			foreach($arrXml['url'] as $key=>$item){
				echo ($key+1).',"'.$item['loc'].'","'.str_replace('HTTP/1.1 ', '', $item['response'][0]).'"';
				if(array_key_exists('redirection', $item)){
					echo ',"'.$item['redirection_location'].'","'.str_replace('HTTP/1.1 ', '', $item['redirection'][0]).'"';
				}
				echo "\r\n";
			}
			echo "\r\n";
		}
		flush();
		ob_end_flush();
		exit;
	}
	flush();
	ob_end_flush();
} 
?><html>
	<head>
		<title></title>
		<style type="text/css">
			.list{float: left;width: 100%;list-style: none;display:block}
			.list-header{}
			.list-item{}
			.id-header{min-width: 30px;float: left;}
			.link-header{min-width: 500px;float: left;}
			.status-header{min-width: 100px;}
			.id{min-width: 30px;float: left;}
			.link{float: left;min-width: 500px;overflow: hidden;width:500px;height:20px}
			.status{min-width: 100px;}
			.list-item-list{display: block;list-style: none;}
			.list-header-list{display: block;list-style: none;}
		</style>
	</head>
	<body>
		<?php
		foreach($sitedata as $arrXml){
		?>
		<h1>Analysis of <?php echo $arrXml['sitemap'] ?></h1>
		<ul class="list">
			<li class="list-header"><ul class="list-header-list">
			<li class="id-header">ID</li><li class="link-header">Link</li><li class="status-header">Status</li>
			</ul></li>
			<?php
			foreach($arrXml['url'] as $key=>$item){
				echo '<li class="list-item"><ul class="list-item-list"><li class="id">'.($key+1).'</li>';
				echo '<li class="link" title="'.$item['loc'].'">'.$item['loc'].'</li>';
				echo '<li class="status">'.str_replace('HTTP/1.1 ', '', $item['response'][0]).'</li></ul></li>';
				if(array_key_exists('redirection', $item)){
					echo '<li class="list-item"><ul class="list-item-list"><li class="id">-</li>';
					echo '<li class="link" title="'.$item['redirection_location'].'">'.$item['redirection_location'].'</li>';
					echo '<li class="status">'.str_replace('HTTP/1.1 ', '', $item['redirection'][0]).'</li></ul></li>';
				}
				echo '<br/>';
			}
			?>
		</ul>
		<?php
		}
		?>
	</body>
</html>