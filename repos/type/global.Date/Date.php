<?php

class Date extends Type
{
	static protected $language = array ('en_US' => 'en', 'pt_BR' => 'pt', 'es_ES' => 'es');

	protected $firstYear = 0;

	protected $lastYear = 0;

	protected $showTime = FALSE;

	protected $showAge = FALSE;

	protected $value = array (0, 0, 0);

	protected $time = array (0, 0, 0);

	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);

		$this->setBind (TRUE);

		$this->setBindType (PDO::PARAM_STR);

		$this->value = array (0, 0, 0);

		if (array_key_exists ('value', $field))
			if (trim ($field ['value']) == '[now]')
				$this->setValue (explode ('-', date ('d-m-Y')));
			else
				$this->setValue (explode ('-', $field ['value']));

		if (array_key_exists ('first-year', $field))
			if (!is_numeric ($field ['first-year']) && trim ($field ['first-year']) == '[now]')
				$this->setFirstYear (date ('Y'));
			else
				$this->setFirstYear ($field ['first-year']);

		if (array_key_exists ('last-year', $field))
			if (!is_numeric ($field ['last-year']) && trim ($field ['last-year']) == '[now]')
				$this->setLastYear (date ('Y'));
			else
				$this->setLastYear ($field ['last-year']);

		if (array_key_exists ('show-time', $field))
			$this->setShowTime (strtoupper ($field ['show-time']) == 'TRUE' ? TRUE : FALSE);

		if (array_key_exists ('show-age', $field))
			$this->setShowAge (strtoupper ($field ['show-age']) == 'TRUE' ? TRUE : FALSE);
	}

	public function setValue ($value)
	{
		if (!is_array ($value))
		{
			$value = explode ('-', $value);

			$time = explode (' ', $value [2]);

			if (sizeof ($time) > 1)
			{
				$value [2] = $time [0];

				$time = explode (':', $time [1]);

				array_walk ($time, array ($this, 'toInteger'));

				$this->time = $time;
			}

			array_walk ($value, array ($this, 'toInteger'));
		}

		$this->value = $value;
	}

	public function setFirstYear ($firstYear)
	{
		$firstYear = trim ($firstYear);

		if ($firstYear == '[now]')
			$firstYear = date ('Y');

		$this->firstYear = (int) $firstYear;
	}

	public function getFirstYear ()
	{
		return $this->firstYear;
	}

	public function setLastYear ($lastYear)
	{
		$lastYear = trim ($lastYear);

		if ($lastYear == '[now]')
			$lastYear = date ('Y');

		$this->lastYear = (int) $lastYear;
	}

	public function getLastYear ()
	{
		return $this->lastYear;
	}

	public function setShowTime ($showTime)
	{
		$this->showTime = (bool) $showTime;
	}

	public function showTime ()
	{
		return (bool) $this->showTime;
	}

	public function setShowAge ($showAge)
	{
		$this->showAge = (bool) $showAge;
	}

	public function showAge ()
	{
		return (bool) $this->showAge;
	}

	public function setTime ($value)
	{
		if (!is_array ($value))
		{
			$value = explode (':', $value);

			array_walk ($value, array ($this, 'toInteger'));
		}

		$this->time = $value;
	}

	public function getTime ()
	{
		return $this->time;
	}

	public function isEmpty ()
	{
		$value = $this->getValue ();

		if (!is_array ($value))
			return TRUE;

		if (sizeof ($value) == 3)
		{
			for ($i = 0; $i < 3; $i++)
				if (!is_numeric ($value [$i]) || (int) $value [$i] <= 0)
					return TRUE;
		}
		elseif (sizeof ($value) == 2)
		{
			for ($j = 0; $j < 2; $j++)
				for ($i = 0; $i < 3; $i++)
					if (!is_numeric ($value [$j][$i]) || (int) $value [$j][$i] <= 0)
						return TRUE;
		}
		else
			return TRUE;

		return FALSE;
	}

	public function getUnixTime ()
	{
		$d = $this->getValue ();

		$t = $this->getTime ();

		return (int) mktime ((int) $t [0], (int) $t [1], (int) $t [2], (int) $d [1], (int) $d [0], (int) $d [2]);
	}

	public function __toString ()
	{
		if ($this->isEmpty ())
			return '';

		if (sizeof ($this->getValue ()) == 3)
		{
			$buffer = strftime ('%x', $this->getUnixTime ());

			if ($this->showTime ())
				$buffer .= ' '. strftime ('%X', $this->getUnixTime ());

			if ($this->showAge ())
			{
				$value = new DateTime ('@'. $this->getUnixTime ());
				$today = new DateTime ();

				$buffer .= ' ~'. $value->diff ($today)->format ('%y') .' '. __ ('year(s)');
			}

			return $buffer;
		}

		$v = $this->getValue ();

		$from = strftime ('%x', (int) mktime (0, 0, 0, (int) $v [0][1], (int) $v [0][0], (int) $v [0][2]));
		$to = strftime ('%x', (int) mktime (0, 0, 0, (int) $v [1][1], (int) $v [1][0], (int) $v [1][2]));

		return __ ('From') .' '. $from .' '. __ ('to') .' '.$to;
	}

	public static function toInteger (&$item, $key)
	{
		$item = (int) $item;
	}

	public static function toString (&$item, $key)
	{
		$item = $item < 10 ? '0'. (string) $item : (string) $item;
	}

	public static function getLanguage ()
	{
		return array_key_exists (Localization::singleton ()->getLanguage (), self::$language) ? self::$language [Localization::singleton ()->getLanguage ()] : reset (self::$language);
	}
}
