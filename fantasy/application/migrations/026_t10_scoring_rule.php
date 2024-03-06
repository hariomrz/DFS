<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_T10_scoring_rule extends CI_Migration {

	public function up() {
		$this->db->delete(MASTER_SCORING_RULES,array("format" => "4"));

		$sql = "INSERT INTO ".$this->db->dbprefix(MASTER_SCORING_RULES)." (`master_scoring_category_id`, `format`, `score_position`, `en_score_position`, `hi_score_position`, `guj_score_position`, `fr_score_position`, `ben_score_position`, `pun_score_position`, `score_points`, `points_unit`, `meta_key`, `meta_key_alias`) VALUES
			(14, 4, 'For being part of the Starting XI', 'For being part of the Starting XI', 'प्लेयिंग 11 में होने के लिए', 'શરૂઆતમાં 11 ભાગ બનવા માટે', 'Pour avoir fait partie du XI de départ', 'শুরুর একাদশের অংশ হওয়ার জন্য', 'ਸ਼ੁਰੂਆਤੀ ਇਲੈਵਨ ਦਾ ਹਿੱਸਾ ਬਣਨ ਲਈ', 4, 0, 'PLAYING_X1', ''),
			(14, 4, 'For every run scored', 'For every run scored', 'हर रन के लिए', 'દરેક રન માટે', 'Pour chaque point marqué', 'প্রতিটি রান জন্য', 'ਹਰ ਰਨ ਲਈ', 1, 0, 'EVERY_RUN', ''),
			(14, 4, 'Wicket (excluding run-out)', 'Wicket (excluding run-out)', 'विकेट (रन-आउट छोड़कर )', 'વિકેટ્સ (રન-આઉટ સિવાય)', 'Wicket (hors run-out)', 'উইকেট (রান আউট বাদে)', 'ਵਿਕਟ (ਰਨ ਆ outਟ ਨੂੰ ਛੱਡ ਕੇ)', 25, 0, 'WICKET', ''),
			(14, 4, 'Catch', 'Catch', 'कैच', 'કૅચસ', 'Capture', 'ধরা', 'ਫੜੋ', 8, 0, 'CATCH', ''),
			(14, 4, 'Stumping', 'Stumping', 'स्टम्पिंग', 'સ્ટમપિંગ', 'Moignon', 'Stumping', 'ਸਟੰਪਿੰਗ', 12, 0, 'STUMPING', ''),
			(14, 4, 'Run-out (thrower)', 'Run-out (thrower)', 'रन​-आउट (फेंकने वाला)', 'રન આઉટ (ફેંકનાર)', 'Run-out (lanceur)', 'রান আউট (থ্রোয়ার)', 'ਰਨ ਆ outਟ (ਸੁੱਟਣ ਵਾਲਾ)', 8, 0, 'RUN_OUT_THROWER', ''),
			(14, 4, 'Run-out (catcher)', 'Run-out (catcher)', 'रन​-आउट (पकड़ने वाला)', 'રન આઉટ (કેચર)', 'Rupture (receveur)', 'রান আউট (ক্যাচার)', 'ਰਨ ਆ outਟ (ਕੈਚਰ)', 4, 0, 'RUN_OUT_CATCHER', ''),
			(14, 4, 'Run-out', 'Run-out', 'रन आउट', 'રન આઉટ', 'S\'épuiser', 'ঝড়তি-পড়তি', 'ਭੱਜ ਜਾਓ', 12, 0, 'RUN_OUT', ''),
			(14, 4, 'Dismissal for duck (batsmen, wicket-keeper and all-rounders)', 'Dismissal for duck (batsmen, wicket-keeper and all-rounders)', 'शून्य पर आउट होने पर (बैट्समैन/ विकेट कीपर, और आल राउंडर के लिए मान्य)', 'ડક (બેટ્સમેન વિકેટકીપર અને ઓલ-રાઉન્ડર)', 'Licenciement pour canard (batteurs, guichetier et polyvalent)', 'হাঁসের হয়ে ডিসমিসাল (ব্যাটসম্যান, উইকেট কিপার এবং অলরাউন্ডার)', 'ਡਕ ਲਈ ਬਰਖਾਸਤਗੀ (ਬੱਲੇਬਾਜ਼, ਵਿਕਟ ਕੀਪਰ ਅਤੇ ਆਲਰਾ roundਂਡਰ)', -2, 0, 'DUCK', ''),
			(15, 4, 'Every boundary hit', 'Every boundary hit', 'हर चौके के लिये', 'બધા ચોગ્ગા માટે', 'Chaque limite atteinte', 'প্রতিটি বাউন্ডারি হিট', 'ਹਰ ਸੀਮਾ ਹਿੱਟ', 1, 0, 'EVERY_FOUR', ''),
			(15, 4, 'Every six hit', 'Every six hit', 'हर चौके के लिये', 'બધા ચોગ્ગા માટે', 'Tous les six coups', 'প্রতি ছয়টি হিট', 'ਹਰ ਛੇ ਹਿੱਟ', 2, 0, 'EVERY_SIX', ''),
			(15, 4, '30 Run Bonus', '30 Run Bonus', '30 रन बोनस', '30 રન બોનસ', 'Bonus de 30 courses', '30 রান বোনাস', '30 ਚਲਾਓ ਬੋਨਸ', 8, 0, 'THIRTY_RUN', ''),
			(15, 4, '50 Run Bonus', '50 Run Bonus', '50 रन बोनस', '50 રન બોનસ', 'Bonus de 50 courses', '50 রান বোনাস', '50 ਚਲਾਓ ਬੋਨਸ', 16, 0, 'HALF_CENTURY', ''),
			(15, 4, 'Maiden over', 'Maiden over', 'मेडन ओवर', ' મેઇડન ઓવરમાં', 'Maiden over', 'মেয়ের উপর', 'ਕੁਆਰੀ', 16, 0, 'MAIDEN_OVER', ''),
			(15, 4, '2 wickets', '2 wickets', '2 विकेट', '3 વિકેટથી', '2 guichets', '2 উইকেট', '2 ਵਿਕਟਾਂ', 8, 0, 'TWO_WICKET', ''),
			(15, 4, '3 wickets', '3 wickets', '3 विकेट', '3 વિકેટથી', '3 guichets', '3 উইকেট', '3 ਵਿਕਟਾਂ', 16, 0, 'THREE_WICKET', ''),
			(16, 4, 'Enter no of overs for which below scoring rules will be applicable', 'Enter no of overs for which below scoring rules will be applicable', 'न्यूनतम ओवर जिसके बाद ही निम्नलिखित पॉइंटस मान्य होंगे', 'Jiske પર સંખ્યા ટેસ્ટામેન્ટ ફટકારી લાગુ', 'Entrez le nombre de overs pour lesquels les règles de notation ci-dessous seront applicables', 'নীচে স্কোরিং বিধি প্রযোজ্য হবে এমন কোনও ওভার প্রবেশ করান না', 'ਕੋਈ ਓਵਰ ਦਾਖਲ ਕਰੋ ਜਿਸ ਦੇ ਲਈ ਹੇਠਾਂ ਦਿੱਤੇ ਸਕੋਰਿੰਗ ਨਿਯਮ ਲਾਗੂ ਹੋਣਗੇ', 1, 0, 'MINIMUM_BOWLING_OVER', ''),
			(16, 4, 'Below 6 runs per over', 'Below 6 runs per over', 'प्रति ओवर 6 रन के नीचे', 'ઉપર પ્રતિ 6 રન હેઠળ', 'Moins de 6 runs par over', 'ওভার প্রতি 6 রান নীচে', '6 ਓਵਰਾਂ ਦੇ ਹੇਠਾਂ', 6, 0, 'BELOW_6', ''),
			(16, 4, 'Between 6 and 6.99 runs per over', 'Between 6 and 6.99 runs per over', '6 और 6.99 के बीच प्रति ओवर रन', 'ઉપર 6 વચ્ચે દીઠ 4.99 રન', 'Entre 6 et 6,99 passages par over', '6 থেকে ওভারে 6.99 রান', 'ਓਵਰ ਦੇ ਵਿਚਕਾਰ 6 ਅਤੇ 6.99 ਦੌੜਾਂ', 4, 0, 'BETWEEN_6_699', ''),
			(16, 4, 'Between 7 and 8 runs per over', 'Between 7 and 8 runs per over', '7 और 8 के बीच प्रति ओवर रन', 'ઉપર 7 વચ્ચે દીઠ 8 રન', 'Entre 7 et 8 passages par over', 'ওভার প্রতি 7 এবং 8 রান', 'ਪ੍ਰਤੀ ਓਵਰ ਵਿੱਚ 7 ਅਤੇ 8 ਦੌੜਾਂ ਦੇ ਵਿਚਕਾਰ', 2, 0, 'BETWEEN_7_8', ''),
			(16, 4, 'Between 11 and 12 runs per over', 'Between 11 and 12 runs per over', '11 और 12 के बीच प्रति ओवर रन', '11 વચ્ચે દીઠ 12 રન', 'Entre 11 et 12 passages par over', 'ওভার প্রতি 11 এবং 12 রান মধ্যে', '11 ਤੋਂ 12 ਦੌੜਾਂ ਪ੍ਰਤੀ ਓਵਰ ਦੇ ਵਿਚਕਾਰ', -2, 0, 'BETWEEN_11_12', ''),
			(16, 4, 'Between 12.01 and 13 runs per over', 'Between 12.01 and 13 runs per over', '12.01 और 13 के बीच प्रति ओवर रन', '12.01 વચ્ચે દીઠ 13 રનની', 'Entre 12.01 et 13 passages par over', '12.01 এবং 13 ওভারের মধ্যে 11 রান', 'ਦੇ ਵਿਚਕਾਰ 12.01 ਅਤੇ 13 ਦੌੜਾਂ ਪ੍ਰਤੀ ਓਵਰ', -4, 0, 'BETWEEN_121_13', ''),
			(16, 4, 'Above 13 runs per over', 'Above 13 runs per over', 'प्रति ओवर 13 रनों से ऊपर', 'એક ઓવર સુધી 13 રન', 'Plus de 13 passages par over', 'ওভার প্রতি 13 রান উপরে', 'ਪ੍ਰਤੀ ਓਵਰ ਤੋਂ ਉੱਪਰ 13 ਦੌੜਾਂ', -6, 0, 'ABOVE_13', ''),
			(17, 4, 'Enter no. of minimum balls for which below scoring rules will be applicable (Except Bowlers)', 'Enter no. of minimum balls for which below scoring rules will be applicable (Except Bowlers)', 'न्यूनतम बॉल जिन्हें खेलने के बाद निम्नलिखित पॉइंटस मान्य होंगे (गेंदबाजों को छोड़ कर)', 'થોડી બોલમાં ટોચ (બોલરો સિવાય) સ્કોરિંગ નિયમો લાગુ કરશે', 'Entrez no. de balles minimales pour lesquelles les règles de score ci-dessous seront applicables (sauf quilleurs)', 'নং প্রবেশ করুন। ন্যূনতম বলের জন্য যার জন্য নীচে স্কোরিংয়ের নিয়ম প্রযোজ্য (বোলার ব্যতীত)', 'ਦਰਜ ਕਰੋ. ਘੱਟੋ ਘੱਟ ਗੇਂਦਾਂ ਦੀ ਜਿਹਨਾਂ ਲਈ ਹੇਠਲੇ ਸਕੋਰਿੰਗ ਨਿਯਮ ਲਾਗੂ ਹੋਣਗੇ (ਗੇਂਦਬਾਜ਼ਾਂ ਨੂੰ ਛੱਡ ਕੇ)', 5, 0, 'MINIMUM_BALL', ''),
			(17, 4, 'Between 90 and 99.9 runs per 100 balls', 'Between 90 and 99.9 runs per 100 balls', '90 और 99.9 के बीच 100 गेंदों प्रति रन (औसत​)', 'એક નકલ સાથે 90 વચ્ચે અને 99.9 100 બોલમાં (સરેરાશ)', 'Entre 90 et 99,9 pistes pour 100 balles', '100 বল প্রতি 90 এবং 99.9 রান মধ্যে', 'ਪ੍ਰਤੀ 100 ਗੇਂਦਾਂ ਵਿੱਚ 90 ਅਤੇ 99.9 ਦੌੜਾਂ ਦੇ ਵਿੱਚਕਾਰ', -2, 0, 'BETWEEN_90_999', ''),
			(17, 4, 'Between 80 and 89.9 runs per 100 balls', 'Between 80 and 89.9 runs per 100 balls', '80 और 89.9 के बीच 100 गेंदों प्रति रन (औसत​)', 'એક નકલ સાથે 80 વચ્ચે અને 89.9 100 બોલમાં (સરેરાશ)', 'Entre 80 et 89,9 pistes pour 100 balles', '100 বল প্রতি 80 এবং 89.9 রান মধ্যে', 'ਪ੍ਰਤੀ 100 ਗੇਂਦਾਂ ਵਿੱਚ 80 ਅਤੇ 89.9 ਦੌੜਾਂ ਦੇ ਵਿੱਚਕਾਰ', -4, 0, 'BETWEEN_80_899', ''),
			(17, 4, 'Below 80 runs per 100 balls', 'Below 80 runs per 100 balls', '80 रन 100 गेंदों प्रति के नीचे औसत​', 'દીઠ નીચે એવરેજ સાથે 80 થી 100 બોલમાં', 'Moins de 80 pistes pour 100 balles', '100 বল প্রতি 80 রান নীচে', '100 ਦੌੜਾਂ ਪ੍ਰਤੀ 80 ਦੌੜਾਂ ਤੋਂ ਘੱਟ', -6, 0, 'BELOW_80', '');";
		$this->db->query($sql);

	}

	public function down() {

	}

}