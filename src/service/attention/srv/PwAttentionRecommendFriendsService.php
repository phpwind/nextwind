<?php

/**
 * 可能认识的人  推荐关注
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class PwAttentionRecommendFriendsService {
	
	/**
	 * 更新某用户潜在好友
	 * 
	 * @param int $uid
	 */
	public function updateRecommendFriend($uid){
		$uid = intval($uid);
		if ($uid < 1) return false;
		$friends = $this->_getAttentionDs()->getFriendsByUid($uid);
		if ($friends) {
			$fuids = explode(',', $friends['touids']);
			$uids = array();
			$fFriends = $this->_getAttentionDs()->fetchFriendsByUids($fuids);
			$attentionDs = $this->_getAttentionDs();
			$potentialFriends = array();
			foreach ($fuids as $fuid) {
				//A用户的好友B, 没有好友
				if (!isset($fFriends[$fuid])) continue;
				//A用户的好友B的好友
				$bFriends = explode(',', $fFriends[$fuid]['touids']);
				$cFriends = $this->_getAttentionDs()->fetchFriendsByUids($bFriends);
				foreach ($bFriends as $f1) {
					//B用户的好友F1没有好友
					if (!isset($cFriends[$f1]) || $f1 == $uid) continue;
					//B用户的好友F1已经是A的好友
					if (in_array($f1, $fuids)) continue;
					//A 用户已关注 F1
					if ($attentionDs->isFollowed($uid, $f1)) continue;
					$f1Friends = explode(',', $cFriends[$f1]['touids']);
					//F1 和 A的共同好友
					$joinFriends = array_intersect($fuids,$f1Friends);
					if (!$joinFriends) continue;
					if ($potentialFriends[$f1]) {
						$potentialFriends[$f1] = array_merge($potentialFriends[$f1], $joinFriends);
					} else {
						$potentialFriends[$f1] = $joinFriends;
					}
				}
			}
			$this->_getRecommendDs()->deleteRecommendFriend($uid);
			$data = array();
			foreach ($potentialFriends as $k2=>$v2) {
				if ($k2 == $uid) continue;
				$v2 = array_unique($v2);
				foreach ($v2 as $v3) {
					$data[] = array(
						'uid' => $uid,
						'recommend_uid' => $k2,
						'same_uid' => $v3,
					);
				}
			}
			$this->_getRecommendDs()->batchReplaceRecommendFriend($data);
		}
		$this->_updateCache($uid);
		return true;
	}
	
	/**
	 * 获取推荐用户缓存数据
	 * 
	 * @param int $uid
	 * @return array RecommentUsers
	 */
	public function getRecommentUser(PwUserBo $loginUser) {
		$recommends = $loginUser->info['recommend_friend'];
		if (!$recommends) return array();
		$recommends = explode('|', $recommends);
		$array = array();
		foreach ($recommends as $v) {
			if (!$v) continue;
			list($uid,$username,$cnt,$sameUser) = explode(',', $v);
			$array[$uid] = array(
				'uid' => $uid,
				'username'	=>	$username,
				'cnt'	=>	$cnt
			);
			$sameUser && $array[$uid]['sameUser'] = unserialize($sameUser);
		}
		return $array;
	}
	
	/**
	 * 获取推荐关注的用户
	 * 
	 * @param int $uid
	 * @param int $num
	 * @return array uids
	 */
	public function getPotentialAttention(PwUserBo $loginUser,$num) {
		$recomment = $this->getRecommentUser($loginUser);
		$recommentCount = count($recomment);
		if ($recommentCount >= $num) {
			return array_keys($recomment);
		}
		$num = $num - $recommentCount;
		$uids = $this->getRecommendAttention($loginUser->uid, $num);
		return array_unique(array_keys((array)$recomment) + $uids);
	}

	/**
	 * 根据规则获取推荐关注 | 先从在线用户取数据，大站20个在线用户总有的吧，20个在线用户都没有，那就慢查吧根据发帖数
	 * 
	 * @param $uid 
	 * @param $num 
	 * @return array
	 */
	public function getRecommendAttention($uid,$num) {
		$uids = $this->_getOnlneUids(20);
		$onlineCount = count($uids);
		if ($onlineCount < $num) {
			$num = $num - $onlineCount;
			Wind::import('SRV:user.vo.PwUserSo');
			$vo = new PwUserSo();
			$vo->orderbyPostnum(true);
			$searchDs = Wekit::load('SRV:user.PwUserSearch');
			$result = $searchDs->searchUser($vo, $num);
			$uids = array_merge($uids, array_keys($result));
		}
		return array_unique(array_diff($uids, array($uid)));
	}
	
	/** 
	 * 组装关注用户数据
	 * 
	 * @param int $uid 用户uid
	 * @param array $recommendUids 推荐关注uids
	 * @param int $num
	 * @return array
	 */
	public function buildUserInfo($uid,$recommendUids,$num) {
		$attentions = $this->_getAttentionDs()->fetchFollows($uid,$recommendUids);
		$uids = array_diff($recommendUids,array($uid),array_keys($attentions));
		$uids = array_slice($uids, 0, $num);
		return $this->_getUser()->fetchUserByUid($uids);
	}
	
	public function getRecommendUsers($uid,$num) {
		$uids = $this->getRecommendAttention($uid,2*$num);
		return $this->buildUserInfo($uid, $uids, $num);
	}
	
	/** 
	 * 获取在线用户
	 * 
	 * @param int $num
	 * @return array uids
	 */
	private function _getOnlneUids($num) {
		$onlineCount = $this->_getOnlineCountService()->getUserOnlineCount();
		if ($onlineCount > 0) {
			$start = $onlineCount > $num ? rand(0, $onlineCount - $num) : 0;
			$onlineUser = $this->_getUserOnlineDs()->getInfoList('',$start,$num);
			$onlineUids = array_keys($onlineUser);
		}
		return $onlineUids ? $onlineUids : array();
	}
	
	/** 
	 * 更新推荐用户缓存
	 * 
	 * @param int $uid
	 * @return bool
	 */
	private function _updateCache($uid) {
		$pFriends = $this->_getRecommendDs()->getRecommendFriend($uid);
		$userInfo = $uids  = array();
		if ($pFriends) {
			foreach($pFriends as $v) {
				$uids[] = $v['recommend_uid'];
				$sameUids = explode(',',$v['same_uids']);
				$sameUids && $sameUids = array_slice($sameUids,0,3);
				$uids = array_merge($uids,$sameUids);
			}
		}

		$uids && $userInfo = Wekit::load('user.PwUser')->fetchUserByUid($uids);
		if (!$uids) {
			$userInfo = $this->getRecommendUsers($uid,5);
		}
		$array = $tmpSame = array();
		foreach ($pFriends as $v) {
			if (!$userInfo[$v['recommend_uid']] || $uid == $v['recommend_uid']) continue;
			$tmpArray['username'] = $userInfo[$v['recommend_uid']]['username'];
			$sameUids = explode(',',$v['same_uids']);
			if ($sameUids) {
				$sameUids = array_slice($sameUids,0,3);
				foreach ($sameUids as $u) {
					$userInfo[$u]['username'] && $tmpSame[$u] = $userInfo[$u]['username'];
				}
			}
			$tmpArray['uid'] = $v['recommend_uid'];
			$tmpArray['cnt'] = $v['cnt'];
			$tmpArray['sameUser'] = $tmpSame;
			$array[] = $tmpArray;
		}
		
		if (!$array) {
			foreach ($userInfo as $user) {
				$tmpArray['uid'] = $user['uid'];
				$tmpArray['username'] = $user['username'];
				$tmpArray['sameUser'] = $tmpSame;
				$array[$user['uid']] = $tmpArray;
			}
		}
		$this->_updateUserData($uid, $array);
		return true;
	}

	private function _updateUserData($uid,$array) {
		if (!$array) return false;
		// 更新用户data表信息
		$userData = array_slice($array, 0, 3);
		Wind::import('SRV:user.dm.PwUserInfoDm');
		$dm = new PwUserInfoDm($uid);
		$dm->setRecommendFriend($this->formatData($userData));
		$this->_getUser()->editUser($dm, PwUser::FETCH_DATA);
		if ($array) {
			$fields = array();
			foreach ($array as $v) {
				$_temp['uid'] = $uid;
				$_temp['recommend_uid'] = $v['uid'];
				$_temp['cnt'] = $v['cnt'];
				$_temp['recommend_user'] = serialize($v);
				$fields[$v['uid']] = $_temp;
			}

			$this->_getRecommendFriendsDs()->deleteRecommendFriend($uid);
			$this->_getRecommendFriendsDs()->batchReplaceRecommendFriend($fields);
		}
	}
	
	public function attentionUserRecommend($touid) {
		$loginUser = Wekit::getLoginUser();
		$this->_getRecommendDs()->deleteRecommendFriend($loginUser->uid, $touid);
		$this->_getRecommendFriendsDs()->deleteRecommendFriend($loginUser->uid, $touid);
		$recommend_user = $loginUser->info['recommend_friend'];
		$result = $this->_getRecommendFriendsDs()->getRecommendFriend($loginUser->uid, 3);
		$users = array();
		foreach ($result as $v) {
			$v['recommend_user'] && $users[] = unserialize($v['recommend_user']);
		}
		return $this->formatData($users);
	}
	
	public function formatData($users) {
		if (!$users) return false;
		$user = '';
		$i = 0;
		foreach ($users as $u) {
			$user .= $u['uid'] . ',' . $u['username'] . ',' . $u['cnt'];
			($i == 0 && $u['sameUser']) && $user .= ','.serialize($u['sameUser']);
			$user .= '|';
			$i++;
		}
		return rtrim($user,',');
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return PwAttentionRecommendRecord
	 */
	private function _getRecommendDs(){
		return Wekit::load('attention.PwAttentionRecommendRecord');
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return PwAttentionRecommendFriends
	 */
	private function _getRecommendFriendsDs(){
		return Wekit::load('attention.PwAttentionRecommendFriends');
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return PwAttention
	 */
	private function _getAttentionDs(){
		return Wekit::load('attention.PwAttention');
	}
 	
 	/**
 	 * PwUserOnline
 	 *
 	 * @return PwUserOnline
 	 */
 	private function _getUserOnlineDs() {
 		return Wekit::load('online.PwUserOnline');
 	}
 	
 	/**
 	 * PwUser
 	 *
 	 * @return PwUser
 	 */
	protected function _getUser() {
		return Wekit::load('user.PwUser');
	}
	
 	/**
 	 * PwOnlineCountService
 	 *
 	 * @return PwOnlineCountService
 	 */
 	private function _getOnlineCountService() {
 		return Wekit::load('online.srv.PwOnlineCountService');
 	}
}