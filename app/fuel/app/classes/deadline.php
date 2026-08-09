<?php
/**
 * 期限に関する共通処理
 */
class Deadline
{
	/**
	 * 期限日から残り日数と表示用の情報を計算する
	 *
	 * @param  string $due_date  期限日（Y-m-d形式）
	 * @return array  days / label / class
	 */
	public static function calculate($due_date)
	{
		$today = new \DateTime('today');
		$due   = new \DateTime($due_date);
		$days  = (int) $today->diff($due)->format('%r%a');

		$danger  = \Config::get('freelance.deadline.danger_days');
		$warning = \Config::get('freelance.deadline.warning_days');
		$classes = \Config::get('freelance.deadline_class');

		if ($days < 0)
		{
			$label = abs($days).'日超過';
			$class = $classes['expired'];
		}
		elseif ($days <= $danger)
		{
			$label = '残り'.$days.'日';
			$class = $classes['danger'];
		}
		elseif ($days <= $warning)
		{
			$label = '残り'.$days.'日';
			$class = $classes['warning'];
		}
		else
		{
			$label = '残り'.$days.'日';
			$class = $classes['safe'];
		}

		return array(
			'days'  => $days,
			'label' => $label,
			'class' => $class,
		);
	}
}