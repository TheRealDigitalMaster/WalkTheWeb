<?php
class wtwshopping_stores {
	protected static $_instance = null;
	
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function __construct() {
		global $wtwplugins;
		try {
			$this->initClass();
		} catch (Exception $e) {
			$wtwplugins->serror("plugins:wtw-shopping:functions-wtwshopping_stores.php-construct=".$e->getMessage());
		}
	}	
	
	public function __call ($method, $arguments)  {
		if (isset($this->$method)) {
			call_user_func_array($this->$method, array_merge(array(&$this), $arguments));
		}
	}
	
	public function initClass() {
		global $wtwplugins;
		try {
			
		} catch (Exception $e) {
			$wtwplugins->serror("plugins:wtw-shopping:functions-wtwshopping_stores.php-initClass=".$e->getMessage());
		}
	}
	
	public function saveStore($zstoreid, $zstorename, $zstoreiframes, $zstoreurl, $zstorecarturl, $zstoreproducturl, $zwoocommerceapiurl, $zwoocommercekey, $zwoocommercesecret) {
		global $wtwplugins;
		try {
			if ($wtwplugins->isUserInRole("Admin") || $wtwplugins->isUserInRole("Developer") || $wtwplugins->isUserInRole("Host") || $wtwplugins->isUserInRole("Architect")) {
				$zhostuserid = '';
				if ($wtwplugins->isUserInRole("Host") && $wtwplugins->isUserInRole("Admin") == false) {
					$zhostuserid = $wtwplugins->userid;
				}
				if (!isset($zstoreid) || empty($zstoreid)) {
					$zstoreid = $wtwplugins->getRandomString(16,1);
					$zwtwkey = base64_encode("ck_".$wtwplugins->getRandomString(40,1));
					$zwtwsecret = base64_encode("cs_".$wtwplugins->getRandomString(40,1));

					$wtwplugins->query("
						insert into ".WTWSHOPPING_PREFIX."stores
							(storeid,
							 hostuserid,
							 storename,
							 storeiframes,
							 storeurl,
							 storecarturl,
							 storeproducturl,
							 woocommerceapiurl,
							 wtwkey,
							 wtwsecret,
							 woocommercekey, 
							 woocommercesecret,
							 approveddate,
							 approveduserid,
							 createdate,
							 createuserid,
							 updatedate,
							 updateuserid)
						values
							('".$zstoreid."',
							 '".$zhostuserid."',
							 '".$zstorename."',
							 ".$zstoreiframes.",
							 '".$zstoreurl."',
							 '".$zstorecarturl."',
							 '".$zstoreproducturl."',
							 '".$zwoocommerceapiurl."',
							 '".$zwtwkey."',
							 '".$zwtwsecret."',
							 '".$zwoocommercekey."', 
							 '".$zwoocommercesecret."',
							 now(),
							 '".$wtwplugins->userid."',
							 now(),
							 '".$wtwplugins->userid."',
							 now(),
							 '".$wtwplugins->userid."');");
				} else {
					$zwhere = "where storeid='".$zstoreid."' ";
					if (!empty($zhostuserid)) {
						$zwhere = "where storeid='".$zstoreid."' and hostuserid='".$zhostuserid."' ";
					}
					$wtwplugins->query("
						update ".WTWSHOPPING_PREFIX."stores
						set storename='".$zstorename."',
							storeiframes=".$zstoreiframes.",
							storeurl='".$zstoreurl."',
							storecarturl='".$zstorecarturl."',
							storeproducturl='".$zstoreproducturl."',
							woocommerceapiurl='".$zwoocommerceapiurl."',
							woocommercekey='".$zwoocommercekey."', 
							woocommercesecret='".$zwoocommercesecret."',
							updatedate=now(),
							updateuserid='".$wtwplugins->userid."',
							deleteddate=null,
							deleteduserid='',
							deleted=0 ".$zwhere."
						limit 1;
					");
				}
			}
		} catch (Exception $e) {
			$wtwplugins->serror("plugins:wtw-shopping:functions-wtwshopping_stores.php-saveStore=".$e->getMessage());
		}
		return $zstoreid;
	}
	
	public function saveConnectStore($zstoreid,$zcommunityid,$zbuildingid,$zthingid) {
		global $wtwplugins;
		$zsuccess = false;
		try {
			if ($wtwplugins->isUserInRole("Admin") || $wtwplugins->isUserInRole("Host") || $wtwplugins->isUserInRole("Developer") || $wtwplugins->isUserInRole("Architect")) {
				$zhostuserid = '';
				if ($wtwplugins->isUserInRole("Host") && $wtwplugins->isUserInRole("Admin") == false) {
					$zhostuserid = $wtwplugins->userid;
				}
				if (!empty($zcommunityid) || !empty($zbuildingid) || !empty($zthingid)) {
					$zconnectid = '';
					$zwhere = "where communityid='".$zcommunityid."'
							and buildingid='".$zbuildingid."'
							and thingid='".$zthingid."' ";
					if (!empty($zhostuserid)) {
						$zwhere = "where communityid='".$zcommunityid."'
							and buildingid='".$zbuildingid."'
							and thingid='".$zthingid."' 
							and hostuserid='".$zhostuserid."' ";
					}
					$zresults = $wtwplugins->query("
						select connectid 
						from ".WTWSHOPPING_PREFIX."connectstores
						".$zwhere." 
						limit 1;");
					foreach ($zresults as $zrow) {
						$zconnectid = $zrow["connectid"];
					}
					if ($wtwplugins->hasValue($zconnectid)) {
						$zwhere = "where connectid='".$zconnectid."' ";
						if (!empty($zhostuserid)) {
							$zwhere = "where connectid='".$zconnectid."' 
								and hostuserid='".$zhostuserid."' ";
						}
						$wtwplugins->query("
							update ".WTWSHOPPING_PREFIX."connectstores
							set storeid='".$zstoreid."',
								updatedate=now(),
								updateuserid='".$wtwplugins->userid."',
								deleteddate=null,
								deleteduserid='',
								deleted=0
							".$zwhere." 
							limit 1;");
					} else {
						$zconnectid = $wtwplugins->getRandomString(16,1);
						$wtwplugins->query("
							insert into ".WTWSHOPPING_PREFIX."connectstores
								(connectid,
								 storeid,
								 communityid,
								 buildingid,
								 thingid,
								 hostuserid,
								 createdate,
								 createuserid,
								 updatedate,
								 updateuserid)
								values
								('".$zconnectid."',
								 '".$zstoreid."',
								 '".$zcommunityid."',
								 '".$zbuildingid."',
								 '".$zthingid."',
								 '".$zhostuserid."',
								 now(),
								 '".$wtwplugins->userid."',
								 now(),
								 '".$wtwplugins->userid."');");
					}
					$zsuccess = true;
				}
			}
		} catch (Exception $e) {
			$wtwplugins->serror("plugins:wtw-shopping:functions-wtwshopping_stores.php-saveConnectStore=".$e->getMessage());
		}
		return $zsuccess;
	}

	public function updateStoreKey($zstoreid) {
		global $wtwplugins;
		$zsuccess = false;
		try {
			if ($wtwplugins->isUserInRole("Admin") || $wtwplugins->isUserInRole("Host") || $wtwplugins->isUserInRole("Developer") || $wtwplugins->isUserInRole("Architect")) {
				if ($wtwplugins->hasValue($zstoreid)) {
					$zhostuserid = '';
					if ($wtwplugins->isUserInRole("Host") && $wtwplugins->isUserInRole("Admin") == false) {
						$zhostuserid = $wtwplugins->userid;
					}
					$zwhere = "where storeid='".$zstoreid."' ";
					if (!empty($zhostuserid)) {
						$zwhere = "where storeid='".$zstoreid."' 
							and hostuserid='".$zhostuserid."' ";
					}
					$wtwplugins->query("
						update ".WTWSHOPPING_PREFIX."stores
						set woocommercekey=woocommercekeynew,
							woocommercesecret=woocommercesecretnew,
							deleteddate=null,
							deleteduserid='',
							deleted=0
						".$zwhere."
						limit 1;
					");
					$wtwplugins->query("
						update ".WTWSHOPPING_PREFIX."stores
						set woocommercekeynew='',
							woocommercesecretnew='',
							updatedate=now(),
							updateuserid='".$wtwplugins->userid."'
						where storeid='".$zstoreid."'
						limit 1;
					");
					$zsuccess = true;
				}
			}
		} catch (Exception $e) {
			$wtwplugins->serror("plugins:wtw-shopping:functions-wtwshopping_stores.php-updateStoreKey=".$e->getMessage());
		}
		return $zsuccess;
	}
	
	public function allowConnection($zstoreid) {
		global $wtwplugins;
		$zsuccess = false;
		try {
			if ($wtwplugins->isUserInRole("Admin") || $wtwplugins->isUserInRole("Host") || $wtwplugins->isUserInRole("Developer") || $wtwplugins->isUserInRole("Architect")) {
				if ($wtwplugins->hasValue($zstoreid)) {
					$zhostuserid = '';
					if ($wtwplugins->isUserInRole("Host") && $wtwplugins->isUserInRole("Admin") == false) {
						$zhostuserid = $wtwplugins->userid;
					}
					$zwhere = "where storeid='".$zstoreid."' ";
					if (!empty($zhostuserid)) {
						$zwhere = "where storeid='".$zstoreid."' 
							and hostuserid='".$zhostuserid."' ";
					}
					$zwtwkey = base64_encode("ck_".$wtwplugins->getRandomString(40,1));
					$zwtwsecret = base64_encode("cs_".$wtwplugins->getRandomString(40,1));
					$wtwplugins->query("
						update ".WTWSHOPPING_PREFIX."stores
						set wtwkey='".$zwtwkey."',
							wtwsecret='".$zwtwsecret."',
							createuserid='".$wtwplugins->userid."',
							updatedate=now(),
							updateuserid='".$wtwplugins->userid."',
							deleteddate=null,
							deleteduserid='',
							deleted=0
						".$zwhere."
						limit 1;
					");
					$zstoreurl = "";
					$zwookey = "";
					$zwoosecret = "";
					$zresults = $wtwplugins->query("
						select connectid 
						from ".WTWSHOPPING_PREFIX."stores
						".$zwhere."
						limit 1;");
					foreach ($zresults as $zrow) {
						$zstoreurl = $zrow["storeurl"];
						$zwookey = $zrow["woocommercekey"];
						$zwoosecret = $zrow["woocommercesecret"];
					}
					if ($wtwplugins->hasValue($zwookey) && $wtwplugins->hasValue($zwoosecret)) {
						$zupdateurl = $zstoreurl."/walktheweb/wtwconnection.php?walktheweb_wtwconnection=1&hosturl=".$wtwplugins->domainurl."&wtwkey=".$zwtwkey."&wtwsecret=".$zwtwsecret."&wookey=".$zwookey."&woosecret=".$zwoosecret;
						/* test open the path */
						$wtwplugins->openFilefromURL($zupdateurl);
					}
					$zsuccess = true;
				}
			}
		} catch (Exception $e) {
			$wtwplugins->serror("plugins:wtw-shopping:functions-wtwshopping_stores.php-allowConnection=".$e->getMessage());
		}
		return $zsuccess;
	}
	
	public function deleteStore($zstoreid) {
		global $wtwplugins;
		$zsuccess = false;
		try {
			if ($wtwplugins->isUserInRole("Admin") || $wtwplugins->isUserInRole("Host") || $wtwplugins->isUserInRole("Developer") || $wtwplugins->isUserInRole("Architect")) {
				if ($wtwplugins->hasValue($zstoreid)) {
					$zhostuserid = '';
					if ($wtwplugins->isUserInRole("Host") && $wtwplugins->isUserInRole("Admin") == false) {
						$zhostuserid = $wtwplugins->userid;
					}
					$wtwplugins->query("
						update ".WTWSHOPPING_PREFIX."stores
						set deleteddate=now(),
							deleteduserid='".$wtwplugins->userid."',
							deleted=1
						where storeid='".$zstoreid."'
						limit 1;
					");
					$zsuccess = true;
				}
			}
		} catch (Exception $e) {
			$wtwplugins->serror("plugins:wtw-shopping:functions-wtwshopping_stores.php-deleteStore=".$e->getMessage());
		}
		return $zsuccess;
	}
	
	public function deleteMold($zcommunityid, $zbuildingid, $zthingid, $zmoldid) {
		global $wtwplugins;
		$zsuccess = false;
		try {
			if ($wtwplugins->isUserInRole("Admin") || $wtwplugins->isUserInRole("Developer") || $wtwplugins->isUserInRole("Architect")) {
				if ($wtwplugins->hasValue($zmoldid)) {
					$wtwplugins->query("
						update ".WTWSHOPPING_PREFIX."molds
						set deleteddate=now(),
							deleteduserid='".$wtwplugins->userid."',
							deleted=1
						where moldid='".$zmoldid."'
							and communityid='".$zcommunityid."'
							and buildingid='".$zbuildingid."'
							and thingid='".$zthingid."'
						limit 1;
					");
					$zsuccess = true;
				}
			}
		} catch (Exception $e) {
			$wtwplugins->serror("plugins:wtw-shopping:functions-wtwshopping_stores.php-deleteMold=".$e->getMessage());
		}
		return $zsuccess;
	}
	
	public function saveMold($zcommunityid, $zbuildingid, $zthingid, $zmoldid, $zmoldslug, $zproductid, $zcategoryid, $zallowsearch) {
		global $wtwplugins;
		$zsuccess = false;
		try {
			if ($wtwplugins->isUserInRole("Admin") || $wtwplugins->isUserInRole("Developer") || $wtwplugins->isUserInRole("Architect")) {
				if ($wtwplugins->hasValue($zmoldid)) {
					$zshoppingmoldid = "";
					$zresults = $wtwplugins->query("
						select shoppingmoldid 
						from ".WTWSHOPPING_PREFIX."molds
						where moldid='".$zmoldid."'
							and communityid='".$zcommunityid."'
							and buildingid='".$zbuildingid."'
							and thingid='".$zthingid."'
						order by createdate desc
						limit 1;");
					foreach ($zresults as $zrow) {
						$zshoppingmoldid = $zrow["shoppingmoldid"];
					}
					if ($wtwplugins->hasValue($zshoppingmoldid)) {
						$wtwplugins->query("
							update ".WTWSHOPPING_PREFIX."molds
							set slug='".$zmoldslug."',
								productid='".$zproductid."',
								categoryid='".$zcategoryid."',
								allowsearch=".$zallowsearch.",
								updatedate=now(),
								updateuserid='".$wtwplugins->userid."',
								deleteddate=null,
								deleteduserid='',
								deleted=0
							where shoppingmoldid='".$zshoppingmoldid."'
							limit 1;
						");
					} else {
						$zshoppingmoldid = $wtwplugins->getRandomString(16,1);
						$wtwplugins->query("
							insert into ".WTWSHOPPING_PREFIX."molds
								(shoppingmoldid,
								 moldid,
								 communityid,
								 buildingid,
								 thingid,
								 slug,
								 productid,
								 categoryid,
								 allowsearch,
								 createdate,
								 createuserid,
								 updatedate,
								 updateuserid)
								values
								('".$zshoppingmoldid."',
								 '".$zmoldid."',
								 '".$zcommunityid."',
								 '".$zbuildingid."',
								 '".$zthingid."',
								 '".$zmoldslug."',
								 '".$zproductid."',
								 '".$zcategoryid."',
								 ".$zallowsearch.",
								 now(),
								 '".$wtwplugins->userid."',
								 now(),
								 '".$wtwplugins->userid."');");
					}
					$zsuccess = true;
				}
			}
		} catch (Exception $e) {
			$wtwplugins->serror("plugins:wtw-shopping:functions-wtwshopping_stores.php-saveMold=".$e->getMessage());
		}
		return $zsuccess;
	}
	
}

	function wtwshopping_stores() {
		return wtwshopping_stores::instance();
	}

	/* Global for backwards compatibility. */
	$GLOBALS['wtwshopping_stores'] = wtwshopping_stores();

?>