<?php

namespace Core\Trading;

class NewsFormatter
{
	public static function formatSentimentItems($items)
	{
		$formatted_items = array();
		foreach ($items as $item) {
			$sentiment = strtolower($item['sentiment']);
			$sentiment_display = array(
				'negative' => array('-1', 'text-danger'),
				'positive' => array('+1', 'text-success'),
				'neutral' => array('0.00', 'text-balance')
			);
			if (!isset($sentiment_display[$sentiment])) {
				continue;
			}

			list($range_adjust, $class) = $sentiment_display[$sentiment];
			$text_parts = explode(' ', substr($item['text'], 0, 160));
			array_pop($text_parts);
			$text = implode(' ', $text_parts);
			$link = '<a href="' . $item['url'] . '" class="' . $class . '">' . $text . '...</a>';
			$link = str_replace('=', '((^))', $link);
			$link = str_replace('*', '((_))', $link);
			$link = str_replace('|', '((-))', $link);
			$formatted_items[] = htmlspecialchars($link, ENT_QUOTES, 'UTF-8')
				. ' Sentiment:' . $item['sentiment'] . ' (' . $range_adjust . ')';
		}

		$json = json_encode($formatted_items);
		if ($json === false) {
			throw new \UnexpectedValueException('Unable to encode formatted trading news: ' . json_last_error_msg());
		}

		return $json;
	}
}
