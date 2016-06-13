<?php

/**
 * Translations and text provider
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */
class Token extends Base
{
	const BASE_LANG = 'en';

	const Q_TOKENS = 'tokens';
	
	private $lang = 'en';
	private $languages = array ('en' => 'English');
	private $publishedLanguages = array ('en');
	private $availableLanguages = array ('en');
	
	public function __construct($lang='en')
	{
		parent::__construct();

		$languages = $this->getConfig('languages');
		$publishedLanguages = $this->getConfig('published');
		$availableLanguages = $this->getConfig('available');

		if (is_array ($languages)) {
			$this->languages = $languages;
		}
		if (is_array ($publishedLanguages)) {
			$this->publishedLanguages = $publishedLanguages;
		}
		if (is_array ($availableLanguages)) {
			$this->availableLanguages = $availableLanguages;
		}

		if ($this->isAvailable($lang)) {
			$this->lang = $lang;
		}
	}

	public function isPublished($lang)
	{
		return (DEVMODE || $lang == 'en' || ($lang != 'en' && in_array ($lang, $this->publishedLanguages)));
	}

	public function isAvailable($lang)
	{
		return (DEVMODE || $lang == 'en' || ($lang != 'en' && in_array ($lang, $this->availableLanguages)));
	}

	public function getLanguages()
	{
		return $this->languages;
	}

	public function getListToTranslate($lang)
	{
		if (!$lang || !$this->isAvailable($lang)) {
			$lang = $this->lang;
		}

		$query = "select t.* from tokens t join tokens te on t.token_hash=te.token_hash and t.brand=te.brand and t.token_value=te.token_value and te.lang='en' where t.brand=:brand and t.lang=:lang";
		$st = $this->db()->prepare($query);

		$st->bindValue(':brand', BRAND, PDO::PARAM_STR);
		$st->bindValue(':lang', $lang, PDO::PARAM_STR);

		$st->execute();

		$ret = array();
		while ($t = $st->fetch(PDO::FETCH_ASSOC)) {
			$ret[] = $t;
		}

		return $ret;
	}

	public function getList($lang=null)
	{
		if (!$lang || !$this->isAvailable($lang)) {
			$lang = $this->lang;
		}

		$query = "select * from tokens where brand=:brand and lang=:lang";
		$st = $this->db()->prepare($query);

		$st->bindValue(':brand', BRAND, PDO::PARAM_STR);
		$st->bindValue(':lang', $lang, PDO::PARAM_STR);

		$st->execute();

		$ret = array();
		while ($t = $st->fetch(PDO::FETCH_ASSOC)) {
			$ret[] = $t;
		}

		return $ret;
	}
	
	public function get($name, $lang=null)
	{
		if (!$lang || !$this->isAvailable($lang)) {
			$lang = $this->lang;
		}

		if (!preg_match('/^[0-9a-z]{32}$/i', $name)) {
			$key = md5($name);
		} else {
			$key = $name;
		}
		
		$ret = FileStorage::get($key, 'lang-'.$lang);
		
		if (!$ret) {
			$val = $name;
			
			$query = "select * from tokens where brand=:brand and token_hash=:token_hash and lang=:lang";
			$st = $this->db()->prepare($query);

			$st->bindValue(':brand', BRAND, PDO::PARAM_STR);
			$st->bindValue(':token_hash', $key, PDO::PARAM_STR);
			$st->bindValue(':lang', $lang, PDO::PARAM_STR);

			$st->execute();

			if ($t = $st->fetch(PDO::FETCH_ASSOC)) {
				$val = $t['token_value'];
			} else {
				$st = $this->db()->prepare(
						"insert into tokens "
						."(token_hash, token_value, lang, brand) "
						."values "
						."(:token_hash, :token_value, :lang, :brand)"
					);

				$st->bindValue(':token_hash', $key, PDO::PARAM_STR);
				$st->bindValue(':token_value', $val, PDO::PARAM_LOB);
				$st->bindValue(':lang', $lang, PDO::PARAM_STR);
				$st->bindValue(':brand', BRAND, PDO::PARAM_STR);

				$st->execute();
			}
			
			FileStorage::create($key, $val, 'lang-'.$lang, true);

			return $val;
		}
		
		return $ret;
	}

	public function update($key, $lang, $translation)
	{
		if (!$lang || $lang == 'en') {
			return;
		}

		$query = "select * from tokens where brand=:brand and token_hash=:token_hash and lang='en'";
		$st = $this->db()->prepare($query);

		$st->bindValue(':token_hash', $key, PDO::PARAM_STR);
		$st->bindValue(':brand', BRAND, PDO::PARAM_STR);
		$st->execute();

		if ($t = $st->fetch(PDO::FETCH_ASSOC)) {
			$query = "select * from tokens where brand=:brand and token_hash=:token_hash and lang=:lang";
			$st = $this->db()->prepare($query);

			$st->bindValue(':token_hash', $key, PDO::PARAM_STR);
			$st->bindValue(':lang', $lang, PDO::PARAM_STR);
			$st->bindValue(':brand', BRAND, PDO::PARAM_STR);

			$st->execute();
			if ($t = $st->fetch(PDO::FETCH_ASSOC)) {
				$query = "update tokens set token_value=:token_value where id={$t['id']}";
				$st = $this->db()->prepare($query);
				$st->bindValue(':token_value', $translation, PDO::PARAM_STR);
			} else {
				$query = "insert into tokens (lang, token_hash, token_value, brand) values (:lang, :token_hash, :token_value, :brand)";
				$st = $this->db()->prepare($query);
				$st->bindValue(':token_hash', $key, PDO::PARAM_STR);
				$st->bindValue(':token_value', $translation, PDO::PARAM_STR);
				$st->bindValue(':lang', $lang, PDO::PARAM_STR);
				$st->bindValue(':brand', BRAND, PDO::PARAM_STR);
			}
			$st->execute();
		}

		return true;
	}

	public function delete($hash)
	{
		$query = "delete from tokens where brand=:brand and token_hash=:token_hash";
		$st = $this->db()->prepare($query);

		$st->bindValue(':token_hash', $hash, PDO::PARAM_STR);
		$st->bindValue(':brand', BRAND, PDO::PARAM_STR);
		$st->execute();

		return true;
	}

	public function setLang($lang='en')
	{
		if ($lang && $this->isAvailable($lang)) {
			$this->lang = $lang;
		}
	}
	
	public function getLang()
	{
		return $this->lang;
	}

	public function getDirection()
	{
		if ($this->getLang() == 'ar') {
			return 'rtl';
		} else {
			return 'ltr';
		}
	}

	public function getLangName($lang=null)
	{
		if (!$lang && empty ($this->languages[$lang])) {
			$lang = $this->lang;
		}
		if (!isset ($this->languages[$lang])) {
			return false;
		}
		return $this->languages[$lang];
	}

	public function queueRun($task) {
		$info = explode(':', $task['params']);
		
		if (!isset($info[1])) {
			return false;
		}
		
		if ($info[0] == 'update') {
			$lang = $info[1];
			
			$query = "
				select
				  t1.token_hash, t2.token_value
				from
				  tokens t1
				left join tokens t2 on t1.token_hash=t2.token_hash
				where t1.lang='en' and t2.lang=:lang and brand=:brand";
			
			$st = $this->db()->prepare($query);
			$st->bindValue(':lang', $lang, PDO::PARAM_STR);
			$st->bindValue(':brand', BRAND, PDO::PARAM_STR);
			$st->execute();
			
			while ($f = $st->fetch(PDO::FETCH_ASSOC)) {
				FileStorage::create(
						$f['token_hash'],
						$f['token_value'],
						'lang-'.$lang,
						true);
			}
			
			return true;
		}
		
		return false;
	}

}
