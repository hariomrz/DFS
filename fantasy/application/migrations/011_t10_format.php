<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_T10_format extends CI_Migration {

	public function up() {
		$sql = "INSERT INTO ".$this->db->dbprefix(MASTER_SCORING_RULES)." (`master_scoring_category_id`, `format`, `score_position`, `en_score_position`, `hi_score_position`, `guj_score_position`, `score_points`, `points_unit`, `meta_key`, `meta_key_alias`) VALUES
(14, 4, 'For being part of the Starting XI', 'For being part of the Starting XI', 'प्लेयिंग 11 में होने के लिए', 'શરૂઆતમાં 11 ભાગ બનવા માટે', 2, 0, 'PLAYING_X1', ''),
(14, 4, 'For every run scored', 'For every run scored', 'हर रन के लिए', 'દરેક રન માટે', 0.5, 0, 'EVERY_RUN', ''),
(14, 4, 'Wicket (excluding run-out)', 'Wicket (excluding run-out)', 'विकेट (रन-आउट छोड़कर )', 'વિકેટ્સ (રન-આઉટ સિવાય)', 10, 0, 'WICKET', ''),
(14, 4, 'Catch', 'Catch', 'कैच', 'કૅચસ', 4, 0, 'CATCH', ''),
(14, 4, 'Stumping', 'Stumping', 'स्टम्पिंग', 'સ્ટમપિંગ', 6, 0, 'STUMPING', ''),
(14, 4, 'Run-out (thrower)', 'Run-out (thrower)', 'रन​-आउट (फेंकने वाला)', 'રન આઉટ (ફેંકનાર)', 4, 0, 'RUN_OUT_THROWER', ''),
(14, 4, 'Run-out (catcher)', 'Run-out (catcher)', 'रन​-आउट (पकड़ने वाला)', 'રન આઉટ (કેચર)', 2, 0, 'RUN_OUT_CATCHER', ''),
(14, 4, 'Run-out', 'Run-out', 'रन आउट', 'રન આઉટ', 6, 0, 'RUN_OUT', ''),
(14, 4, 'Dismissal for duck (batsmen, wicket-keeper and all-rounders)', 'Dismissal for duck (batsmen, wicket-keeper and all-rounders)', 'शून्य पर आउट होने पर \n(बैट्समैन/ विकेट कीपर, और आल राउंडर के लिए मान्य)', 'ડક (બેટ્સમેન વિકેટકીપર અને ઓલ-રાઉન્ડર)', -2, 0, 'DUCK', ''),
(15, 4, 'Every boundary hit', 'Every boundary hit', 'हर चौके के लिये', 'બધા ચોગ્ગા માટે', 0.5, 0, 'EVERY_FOUR', ''),
(15, 4, 'Every six hit', 'Every six hit', 'हर चौके के लिये', 'બધા ચોગ્ગા માટે', 1, 0, 'EVERY_SIX', ''),
(15, 4, 'Half century', 'Half century', 'अर्ध शतक', 'અડધી સદી', 4, 0, 'HALF_CENTURY', ''),
(15, 4, 'Century', 'Century', 'शतक', 'સેન્ચ્યુરી', 8, 0, 'CENTURY', ''),
(15, 4, 'Maiden over', 'Maiden over', 'मेडन ओवर', ' મેઇડન ઓવરમાં', 4, 0, 'MAIDEN_OVER', ''),
(15, 4, '4 wickets', '4 wickets', '4 विकेट', '4 વિકેટથી', 4, 0, 'FOUR_WICKET', ''),
(15, 4, '5 wickets', '5 wickets', '5 विकेट', '5 વિકેટથી', 8, 0, 'FIVE_WICKET', ''),
(16, 4, 'Enter no of overs for which below scoring rules will be applicable', 'Enter no of overs for which below scoring rules will be applicable', 'न्यूनतम ओवर जिसके बाद ही निम्नलिखित पॉइंटस मान्य होंगे', 'Jiske પર સંખ્યા ટેસ્ટામેન્ટ ફટકારી લાગુ', 2, 0, 'MINIMUM_BOWLING_OVER', ''),
(16, 4, 'Between 6 and 5 runs per over', 'Between 6 and 5 runs per over', '5 और 6 के बीच प्रति ओवर रन', 'ઉપર 6 વચ્ચે દીઠ 5 રન', 1, 0, 'BETWEEN_5_6', ''),
(16, 4, 'Between 4.99 and 4 runs per over', 'Between 4.99 and 4 runs per over', '4 और 4.99 के बीच प्रति ओवर रन', 'ઉપર 4.99 વચ્ચે દીઠ 4 રન', 2, 0, 'BETWEEN_4_499', ''),
(16, 4, 'Below 4 runs per over', 'Below 4 runs per over', 'प्रति ओवर 4 रन के नीचे', 'ઉપર પ્રતિ 4 રન હેઠળ', 3, 0, 'BELOW_4', ''),
(16, 4, 'Between 9 and 10 runs per over', 'Between 9 and 10 runs per over', '9 और 10 के बीच प्रति ओवर रन', '10 વચ્ચે દીઠ 9 રન', -1, 0, 'BETWEEN_9_10', ''),
(16, 4, 'Between 10.01 and 11 runs per over', 'Between 10.01 and 11 runs per over', '10.01 और 11 के बीच प्रति ओवर रन', '11 વચ્ચે દીઠ 10.01 રનની', -2, 0, 'BETWEEN_101_11', ''),
(16, 4, 'Above 11 runs per over', 'Above 11 runs per over', 'प्रति ओवर 11 रनों से ऊपर', 'એક ઓવર સુધી 11 રન', -3, 0, 'ABOVE_11', ''),
(17, 4, 'Enter no. of minimum balls for which below scoring rules will be applicable (Except Bowlers)', 'Enter no. of minimum balls for which below scoring rules will be applicable (Except Bowlers)', 'न्यूनतम बॉल जिन्हें खेलने के बाद निम्नलिखित पॉइंटस मान्य होंगे (गेंदबाजों को छोड़ कर)', 'થોડી બોલમાં ટોચ (બોલરો સિવાય) સ્કોરિંગ નિયમો લાગુ કરશે', 10, 0, 'MINIMUM_BALL', ''),
(17, 4, 'Between 60 and 70 runs per 100 balls', 'Between 60 and 70 runs per 100 balls', '60 और 70 के बीच100 गेंदों प्रति रन (औसत​)', 'એક નકલ સાથે 60 વચ્ચે અને 70 100 બોલમાં (સરેરાશ)', -1, 0, 'BETWEEN_60_70', ''),
(17, 4, 'Between 50 and 59.9 runs per 100 balls', 'Between 50 and 59.9 runs per 100 balls', '50 और 59.9 के बीच 100 गेंदों प्रति रन (औसत​)', '59.9 વચ્ચે 100 બોલમાં દીઠ 450 રન (સરેરાશ)', -2, 0, 'BETWEEN_50_599', ''),
(17, 4, 'Below 50 runs per 100 balls', 'Below 50 runs per 100 balls', '50 रन 100 गेंदों प्रति के नीचे औसत​', 'દીઠ નીચે એવરેજ સાથે 50 થી 100 બોલમાં', -3, 0, 'BELOW_50', '');";
		$this->db->query($sql);
	}

	public function down() {
		$this->db->delete(MASTER_SCORING_RULES,array("format" => "4"));
	}

}