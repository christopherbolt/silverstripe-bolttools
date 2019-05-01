<?php

class BoltMailHelper {
	
	public $username = '';
	public $usertoken = '';
	
	function __construct($username='', $usertoken='') {
		$this->username = $username;
		$this->usertoken = $usertoken;
	}
	
	function GetResult($requesttype, $requestmethod, $details_xml='') {
		$xml = 
'<xmlrequest>
  <username>'.$this->username.'</username>
  <usertoken>'.$this->usertoken.'</usertoken>
  <requesttype>'.$requesttype.'</requesttype>
  <requestmethod>'.$requestmethod.'</requestmethod>
  <details>
	  '.$details_xml.'
  </details>
</xmlrequest>';
		
		$ch = curl_init('https://www.boltmail.co.nz/boltmail/xml.php'); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); curl_setopt($ch, CURLOPT_POST, 1); curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		
		$result = @curl_exec($ch); 
		//exit($result);
		if($result === false) {
			return array( 
				'Success' => false,
				'ErrorMessage' => "Error performing request. Failed to connect to mailing list server, please try again later.",
				'Data' => ''
			);
		} else {
			$xml_doc = simplexml_load_string($result); 
			if ($xml_doc->status == 'SUCCESS') {
				return array( 
					'Success' => true,
					'ErrorMessage' => "" ,
					'Data' => $xml_doc->data
				);
			} else {
				return array( 
					'Success' => false,
					'ErrorMessage' => (string) $xml_doc->errormessage,
					'Data' => ''
				);
			}
		}
	}
	
	function AddSubscriberToList($email, $listid, $customfields=array()) {
	
		$xml = 
	  '<emailaddress>'.$email.'</emailaddress>
	  <mailinglist>'.$listid.'</mailinglist>
	  <format>html</format>
	  <confirmed>yes</confirmed>';
		if (count($customfields)) {
			  $xml .= '<customfields>';
			  foreach ($customfields as $k => $v) {
				  $xml .= '<item>
					<fieldid>'.$k.'</fieldid>
					<value>'.$v.'</value>
				  </item>';
			  }
			  $xml .= '</customfields>';
		}
		
		$result = $this->GetResult('subscribers', 'AddSubscriberToList', $xml);
		if (!$result['Success'] && is_numeric($result['ErrorMessage'])) {
			// Subscriber is already subscribed!
			// Lets update their details instead, no API exists for this? WTF?
			$result['ErrorMessage'] = 'You are already subscribed to this list.';
		}
		return $result;
	
	}
	
	function GetLists() {
		
		return $this->GetResult('user', 'GetLists', '');
	
	}
	
	function GetArchive($listids, $titleLen=40) {
		if (!is_array($listids)) $listids = array($listids);
		$lists = array();
		//$listids = explode(',', $this->BoltMailListIds);
		foreach ($listids as $listid) {
			$lists[] = 'https://www.boltmail.co.nz/boltmail/rss.php?List='.$listid;	
		}
		
		$feed = new SimplePie();
		// Set which feed to process.
		$feed->set_feed_url($lists);
		// Setup a cahce dir
		$feed->set_cache_location(TEMP_FOLDER);
		// Run SimplePie.
		$feed->init();
		// This makes sure that the content is sent to the browser as text/html and the UTF-8 character set (since we didn't change it).
		//$feed->handle_content_type();
		$duplicates = array();
		$array = array();
		foreach ($feed->get_items() as $item) {
			$url = $item->get_permalink();
			$n = array();
			preg_match("#N=([0-9]+)#smi", $url, $n);
			if (isset($n[1])) {
				if (in_array($n[1],$duplicates)) continue;
				$duplicates[] = $n[1];
			}
			$title = $item->get_title();
			if ($title == 'No email campaigns have been sent') continue;
			if (strlen($title) > $titleLen) {
				$title = substr($title, 0, $titleLen).'...';	
			}
			
			array_push($array, array(
				'FullTitle' => $item->get_title(),
				'Title' => $title,
				'URL' => $url,
				'FormattedDate' => $item->get_date('F Y')
			));
		}
		
		return new DataObjectSet($array);
	}
	
		
}


?>