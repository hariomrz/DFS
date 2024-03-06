<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Soccer_scoring_rules_update extends CI_Migration {

	public function up() 
	{
		//Trasaction start
    	$this->db->trans_strict(TRUE);
    	$this->db->trans_start();
   	
    	//Add two filed in cricket statistics table
    	$sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_SOCCER)." ADD `chancecreated` INT NOT NULL DEFAULT '0' AFTER `own_goals_minutes`, ADD `starting11` INT NOT NULL DEFAULT '0' AFTER `chancecreated`, ADD `substitute` INT NOT NULL DEFAULT '0' AFTER `starting11`, ADD `blockedshot` INT NOT NULL DEFAULT '0' AFTER `substitute`, ADD `interceptionwon` INT NOT NULL DEFAULT '0' AFTER `blockedshot`, ADD `clearance` INT NOT NULL DEFAULT '0' AFTER `interceptionwon`;";
    	$this->db->query($sql);

    	//Add new rules for soccer
    	$sql = "DELETE FROM ".$this->db->dbprefix(MASTER_SCORING_RULES)." WHERE `format` = 1 AND master_scoring_category_id = 18;";
    	$this->db->query($sql);

    	$sql = "INSERT INTO ".$this->db->dbprefix(MASTER_SCORING_RULES)." (`master_scoring_category_id`, `format`, `score_position`, `en_score_position`, `hi_score_position`, `guj_score_position`, `fr_score_position`, `ben_score_position`, `pun_score_position`, `tam_score_position`, `th_score_position`, `score_points`, `points_unit`, `meta_key`, `meta_key_alias`, `ru_score_position`, `id_score_position`, `tl_score_position`, `zh_score_position`, `kn_score_position`) VALUES
		(18, 1, 'In Starting 11', 'In Starting 11', '11 की शुरुआत में', '11 શરૂ કરી રહ્યા છીએ', 'Au départ 11', '11 শুরুতে', '11 ਦੀ ਸ਼ੁਰੂਆਤ ਵਿੱਚ', 'தொடக்க 11 இல்', 'ในการเริ่มต้น 11', 4, 0, 'STARTING_11', '', 'В старте 11', 'Di Mulai 11', 'Sa Simula 11', '在首发 11', 'ಪ್ರಾರಂಭ 11 ರಲ್ಲಿ'),
		(18, 1, 'Coming on as a Substitute', 'Coming on as a Substitute', 'सब्स्टिट्यूट् के रूप में आ रहा है', 'સબસ્ટિટ્યુટ તરીકે આવી રહ્યું છે', 'Venir en tant que suppléant', 'সাবস্টিটিউট হিসাবে আসছে', 'ਇੱਕ ਬਦਲ ਵਜੋਂ ਆ ਰਿਹਾ ਹੈ', 'ஒரு மாற்றாக வருகிறது', 'มาเป็นตัวสำรอง', 2, 0, 'SUBSTITUTE', '', 'Вышел на замену', 'Datang sebagai Pengganti', 'Darating bilang isang Kapalit', '作为替补登场', 'ಬದಲಿಯಾಗಿ ಬರುತ್ತಿದೆ'),
		(18, 1, 'Goal by a striker', 'Goal by a striker', 'एक स्ट्राइकर द्वारा गोल', 'સ્ટ્રાઈકર દ્વારા ધ્યેય', 'But dun attaquant', 'একজন স্ট্রাইকারের লক্ষ্য', 'ਇੱਕ ਸਟਰਾਈਕਰ ਦੁਆਰਾ ਟੀਚਾ', 'ஸ்ட்ரைக்கரின் இலக்கு', 'ประตูโดยกองหน้า', 40, 0, 'GOAL_STRIKER', '', 'Гол нападающего', 'Gol dari seorang striker', 'Layunin ng isang welgista', '前锋的进球', 'ಸ್ಟ್ರೈಕರ್ ಗುರಿ'),
		(18, 1, 'Goal by a mid-fielder', 'Goal by a mid-fielder', 'एक मिड-फील्डर द्वारा गोल', 'મિડ-ફીલ્ડર દ્વારા ગોલ', 'But dun milieu de terrain', 'মিড-ফিল্ডার দ্বারা গোল', 'ਇੱਕ ਮਿਡ-ਫੀਲਡਰ ਦੁਆਰਾ ਟੀਚਾ', 'மிட் பீல்டர் மூலம் கோல்', 'ประตูโดยมิดฟิลด์', 50, 0, 'GOAL_MID_FIELDER', '', 'Гол полузащитника', 'Gol oleh gelandang tengah', 'Layunin ng isang mid-fielder', '一名中场球员的进球', 'ಮಿಡ್ ಫೀಲ್ಡರ್ ಗೋಲು'),
		(18, 1, 'Goal by a defender or goalkeeper', 'Goal by a defender or goalkeeper', 'एक डिफेंडर या गोलकीपर द्वारा गोल', 'કોઈ ડિફેન્ડર અથવા ગોલકીપર દ્વારા ધ્યેય', 'But dun défenseur ou d\'un gardien de but', 'কোনও ডিফেন্ডার বা গোলরক্ষক দ্বারা লক্ষ্য', 'ਇੱਕ ਡਿਫੈਂਡਰ ਜਾਂ ਗੋਲਕੀਪਰ ਦੁਆਰਾ ਟੀਚਾ', 'ஒரு பாதுகாவலர் அல்லது கோல்கீப்பரின் கோல்', 'ประตูโดยกองหลังหรือผู้รักษาประตู', 60, 0, 'GOAL_DEF_GK', '', 'Гол защитника или вратаря', 'Gol oleh bek atau kiper', 'Layunin ng isang defender o goalkeeper', '后卫或守门员的进球', 'ರಕ್ಷಕ ಅಥವಾ ಗೋಲ್‌ಕೀಪರ್‌ನಿಂದ ಗುರಿ'),
		(18, 1, 'Assist', 'Assist', 'सहायता करना', 'સહાય કરો', 'Aider', 'সহায়তা', 'ਸਹਾਇਤਾ ਕਰੋ', 'உதவு', 'ช่วยเหลือ', 20, 0, 'ASSIST', '', 'Помощь', 'Membantu', 'Tulungan', '协助', 'ಸಹಾಯ'),
		(18, 1, 'Shot on Target (Includes Goals)', 'Shot on Target (Includes Goals)', 'लक्ष्य पर शॉट मार दी (गोल सहित)', 'લક્ષ્યાંક પર શોટ (લક્ષ્યો શામેલ છે)', 'Tir cadré (y compris les buts)', 'লক্ষ্যবস্তুতে শট (লক্ষ্যগুলি অন্তর্ভুক্ত)', 'ਨਿਸ਼ਾਨੇ \'ਤੇ ਸ਼ਾਟ (ਟੀਚੇ ਸ਼ਾਮਲ ਕਰਦੇ ਹਨ)', 'இலக்கு மீது சுடப்பட்டது (இலக்குகள் அடங்கும்)', 'ยิงเข้ากรอบ (รวมประตู)', 6, 0, 'SHOT_ON_TARGET', '', 'Выстрел в цель (включая голы)', 'Ditembak tepat sasaran (Termasuk Gol)', 'Kinunan sa Target (May Kasamang Mga Layunin)', '射中目标（包括进球）', 'ಟಾರ್ಗೆಟ್‌ನಲ್ಲಿ ಚಿತ್ರೀಕರಿಸಲಾಗಿದೆ (ಗುರಿಗಳನ್ನು ಒಳಗೊಂಡಿದೆ)'),
		(18, 1, 'Chance Created, The final pass leading to a shot (on target including goals, blocked or off target)', 'Chance Created, The final pass leading to a shot (on target including goals, blocked or off target)', 'चांस क्रिएट किया गया, अंतिम पास जो एक शॉट की ओर ले जाता है (लक्ष्य पर, अवरुद्ध या बंद लक्ष्य सहित)', 'ચાન્સ બનાવ્યો, અંતિમ પાસ શ shotટ તરફ દોરી જાય છે (લક્ષ્ય સહિત લક્ષ્ય પર, અવરોધિત અથવા બંધ લક્ષ્ય પર)', 'Chance créé, la passe finale menant à un tir (sur la cible, y compris les buts, bloquée ou hors cible)', 'সম্ভাবনা তৈরি করা হয়েছে, একটি শট নিয়ে যাওয়ার চূড়ান্ত পাস (লক্ষ্য সহ লক্ষ্য, ব্লকড বা লক্ষ্য ছাড়াই)', 'ਮੌਕਾ ਬਣਾਇਆ ਗਿਆ, ਅੰਤਮ ਪਾਸ ਜੋ ਸ਼ਾਟ ਵੱਲ ਜਾਂਦਾ ਹੈ (ਟੀਚੇ ਸਮੇਤ, ਟੀਚੇ ਸਮੇਤ, ਟਿਕਾਣੇ ਤੇ ਬੰਦ)', 'உருவாக்கப்பட்ட வாய்ப்பு, ஒரு ஷாட்டுக்கு வழிவகுக்கும் இறுதி பாஸ் (இலக்குகள் உள்ளிட்ட இலக்குகளில், தடுக்கப்பட்ட அல்லது ஆஃப் இலக்கு)', 'สร้างโอกาสแล้ว การจ่ายบอลสุดท้ายที่นำไปสู่การยิง (บนเป้าหมายรวมถึงเป้าหมาย บล็อกหรือนอกกรอบ)', 3, 0, 'CHANCE_CREATED', '', 'Chance Created, последний проход, ведущий к выстрелу (в створ, включая голы, заблокирован или вне мишени)', 'Peluang Diciptakan, Umpan terakhir yang mengarah ke tembakan (tepat sasaran termasuk gol, diblok atau tidak tepat sasaran)', 'Chance Created, Ang huling pass na humahantong sa isang pagbaril (sa target kabilang ang mga layunin, na-block o hindi naka-target)', '创造机会，最后一次射门（命中目标，包括进球、被阻挡或偏离目标）', 'ರಚಿಸಲಾದ ಅವಕಾಶ, ಹೊಡೆತಕ್ಕೆ ಕಾರಣವಾಗುವ ಅಂತಿಮ ಪಾಸ್ (ಗುರಿಗಳು ಸೇರಿದಂತೆ ಗುರಿ, ನಿರ್ಬಂಧಿತ ಅಥವಾ ಆಫ್ ಗುರಿ)'),
		(18, 1, '5 Passes Completed', '5 Passes Completed', '5 पास पूरे हुए', '5 પાસ પૂર્ણ', '5 passes terminées', '5 টি পাস সম্পন্ন হয়েছে', '5 ਪਾਸ ਪੂਰੇ ਹੋਏ', '5 பாஸ் முடிந்தது', '5 ผ่านเสร็จสมบูรณ์', 1, 0, 'PASSES_COMPLETED', '', 'Завершено 5 проходов', '5 Pass Selesai', '5 Passes Nakumpleto', '5 通行证完成', '5 ಪಾಸ್ಗಳು ಪೂರ್ಣಗೊಂಡಿವೆ'),
		(18, 1, 'Tackle Won', 'Tackle Won', 'टैकल जीता', 'જીત્યાં', 'Tacle gagné', 'জিতল', 'ਜਿੱਤਿਆ', 'சமாளித்தது வென்றது', 'แทคเคิลวอน', 4, 0, 'TACKLE_WON', '', 'Отбор выигранный', 'Menangkan Tackle', 'Nagwagi si Tackle', '铲球赢了', 'ಗೆಲುವು ಗೆದ್ದಿದೆ'),
		(18, 1, 'Interception Won', 'Interception Won', 'इंटरसेप्शन जीता', 'વિક્ષેપ જીત્યો', 'Interception gagnée', 'বাধা পেয়েছে', 'ਰੁਕਾਵਟ ਜਿੱਤੀ', 'இடைமறிப்பு வென்றது', 'สกัดกั้นชนะ', 4, 0, 'INTERCEPTION_WON', '', 'Выигран перехват', 'Intersepsi Menang', 'Nagtagumpay ang Pananaw', '拦截获胜', 'ಪ್ರತಿಬಂಧ ಗೆದ್ದಿದೆ'),
		(18, 1, 'Saves (GK)', 'Saves (GK)', 'बचाता है (जीके)', 'સેવ (જીકે)', 'Sauvegardes (GK)', 'সেভস (জিকে)', 'ਸੇਵ (ਜੀ.ਕੇ.)', 'சேமிக்கிறது (ஜி.கே)', 'ประหยัด (GK)', 6, 0, 'SAVES_GK', '', 'Сейвы (В)', 'Simpan (GK)', 'Makatipid (GK)', '保存 (GK)', 'ಉಳಿಸುತ್ತದೆ (ಜಿಕೆ)'),
		(18, 1, 'Penalty Saved (GK)', 'Penalty Saved (GK)', 'सहेजा गया पैनल्टी  (जीके)', 'પેનલ્ટી સેવ (જીકે)', 'Pénalité économisée (GK)', 'পেনাল্টি সেভড (জিকে)', 'ਪੈਨਲਟੀ ਸੇਵ (ਜੀਕੇ)', 'அபராதம் சேமிக்கப்பட்டது (ஜி.கே)', 'การลงโทษที่บันทึกไว้ (GK)', 50, 0, 'PENALTY_SAVED_GK', '', 'Пенальти сохранено (В)', 'Penalti Diselamatkan (GK)', 'Natipid ang Penalty (GK)', '已保存罚分 (GK)', 'ದಂಡ ಉಳಿಸಲಾಗಿದೆ (ಜಿಕೆ)'),
		(18, 1, 'Clean Sheet GK/DEF (Played +55 mins)', 'Clean Sheet GK/DEF (Played +55 mins)', 'क्लीन शीट जीके/डीईएफ (खेला +55 मिनट)', 'ક્લીન શીટ જીકે / ડીઇએફ (રમ્યા +55 મિનિટ)', 'Clean Sheet GK/DEF (joué +55 min)', 'ক্লিন শিট জিকে / ডিইএফ (খেলা +৫৫ মিনিট)', 'ਕਲੀਨ ਸ਼ੀਟ ਜੀ.ਕੇ. / ਡੀ.ਈ.ਐਫ. (ਖੇਡੀ + 55 ਮਿੰਟ)', 'சுத்தமான தாள் GK / DEF (+55 நிமிடங்கள் விளையாடியது)', 'คลีนชีต GK/DEF (เล่น +55 นาที)', 20, 0, 'CLEAN_SHEET_GK_DEF', '', 'Чистый лист GK / DEF (Сыграно +55 минут)', 'Clean Sheet GK/DEF (Dimainkan +55 menit)', 'Malinis na Sheet GK / DEF (Pinatugtog +55 minuto)', '干净的床单 GK/DEF（已播放 +55 分钟）', 'ಕ್ಲೀನ್ ಶೀಟ್ ಜಿಕೆ / ಡಿಇಎಫ್ (+55 ನಿಮಿಷಗಳು ಆಡಲಾಗಿದೆ)'),
		(18, 1, 'Yellow card', 'Yellow card', 'पिला कार्ड', 'યલો કાર્ડ', 'Carte jaune', 'হলুদ কার্ড', 'ਪੀਲਾ ਕਾਰਡ', 'மஞ்சள் அட்டை', 'ใบเหลือง', -4, 0, 'YELLOW_CARD', '', 'Желтая карточка', 'Kartu kuning', 'Dilaw na kard', '黄牌', 'ಹಳದಿ ಕಾರ್ಡ್'),
		(18, 1, 'Red card', 'Red card', 'लाल कार्ड', 'લાલ કાર્ડ', 'carte rouge', 'লাল কার্ড', 'ਲਾਲ ਕਾਰਡ', 'சிவப்பு அட்டை', 'ใบแดง', -10, 0, 'RED_CARD', '', 'Красная карточка', 'kartu merah', 'Pulang kard', '红牌', 'ಕೆಂಪು ಕಾರ್ಡ್'),
		(18, 1, 'Own goal', 'Own goal', 'स्वयं गोल', 'પોતાનો ધ્યેય', 'But contre son camp', 'নিজস্ব লক্ষ্য', 'ਆਪਣਾ ਟੀਚਾ', 'சொந்த இலக்கு', 'เป้าหมายของเรา', -8, 0, 'OWN_GOAL', '', 'Собственная цель', 'Gol bunuh diri', 'Sariling mithiin', '自己的目标', 'ಸ್ವಂತ ಗುರಿ'),
		(18, 1, 'Goals conceded GK/DEF (on the field when the goal is scored except in case of red carded player)', 'Goals conceded GK/DEF (on the field when the goal is scored except in case of red carded player)', 'गोल ने GK/DEF को स्वीकार किया (रेड कार्ड प्राप्त किए हुए खिलाड़ी के अलावा गोल होने के दौरान मैदान पे होना चाहिए)', 'લક્ષ્યો જીકે / ડીઇએફ સ્વીકારે છે (જ્યારે ગોલ થાય ત્યારે મેદાનમાં)', 'Buts encaissés GK/DEF (sur le terrain lorsque le but est marqué)', 'লক্ষ্যগুলি জিকে / ডিইএফকে সম্মতি জানায় (গোল করার সময় মাঠে)', 'ਟੀਚੇ ਨੇ ਜੀਕੇ / ਡੀਈਐਫ ਨੂੰ ਮੰਨਿਆ (ਮੈਦਾਨ ਵਿਚ ਜਦੋਂ ਗੋਲ ਕੀਤਾ ਜਾਂਦਾ ਹੈ)', 'இலக்குகள் GK / DEF ஐ ஒப்புக் கொண்டன (கோல் அடித்த போது களத்தில்)', 'เสียประตู GK/DEF (ในสนามเมื่อทำประตูได้)', -2, 0, 'GOAL_CONCEDED_GK_DEF', '', 'Пропущенные голы GK / DEF (на поле при забитом голе)', 'Kebobolan gol GK/DEF (di lapangan saat gol dicetak)', 'Ang mga layunin ay sumang-ayon sa GK / DEF (sa patlang kapag nakuha ang layunin)', '失球 GK/DEF（进球时在场上）', 'ಗುರಿಗಳು ಜಿಕೆ / ಡಿಇಎಫ್ ಅನ್ನು ಬಿಟ್ಟುಕೊಟ್ಟವು (ಗೋಲು ಗಳಿಸಿದಾಗ ಮೈದಾನದಲ್ಲಿ)'),
		(18, 1, 'Penalty missed', 'Penalty missed', 'पेनल्टी छूटी', 'પેનલ્ટી ચૂકી', 'Pénalité manquée', 'পেনাল্টি মিস', 'ਜੁਰਮਾਨਾ ਖੁੰਝ ਗਿਆ', 'அபராதம் தவறவிட்டது', 'พลาดจุดโทษ', -20, 0, 'PENALTY_MISSED', '', 'Пенальти пропущен', 'Penalti gagal', 'Pinalampas ang penalty', '罚失', 'ದಂಡ ತಪ್ಪಿದೆ');";
		$this->db->query($sql);
		    	
		//Trasaction end
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE )
		{
		    $this->db->trans_rollback();
		}
		else
		{
		    $this->db->trans_commit();
		}

	}

	public function down() 
	{

	}

}