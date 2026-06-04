<?php

use App\Models\BluffQuestion;
use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $seriesCategories = [
        ['name' => 'مسلسل Game of Thrones', 'slug' => 'game-of-thrones'],
        ['name' => 'مسلسل Dexter', 'slug' => 'dexter'],
        ['name' => 'مسلسل الحفرة', 'slug' => 'the-pit'],
        ['name' => 'مسلسل Vikings', 'slug' => 'vikings'],
        ['name' => 'مسلسل Breaking Bad', 'slug' => 'breaking-bad'],
        ['name' => 'مسلسل Stranger Things', 'slug' => 'stranger-things'],
        ['name' => 'مسلسل Prison Break', 'slug' => 'prison-break'],
        ['name' => 'مسلسل La Casa de Papel', 'slug' => 'la-casa-de-papel'],
    ];

    private array $footballQuestions = [
        ['question_text' => 'من هو الهداف التاريخي لكأس العالم؟', 'correct_answer' => 'ميروسلاف كلوزه'],
        ['question_text' => 'كم مرة فاز منتخب البرازيل بكأس العالم؟', 'correct_answer' => '5'],
        ['question_text' => 'من هو اللاعب الملقب بـ "الظاهرة"؟', 'correct_answer' => 'رونالدو البرازيلي'],
        ['question_text' => 'أي دولة فازت بكأس العالم 2022؟', 'correct_answer' => 'الأرجنتين'],
        ['question_text' => 'في أي سنة أُقيمت أول بطولة كأس عالم؟', 'correct_answer' => '1930'],
        ['question_text' => 'أي نادٍ فاز بدوري أبطال أوروبا 2023؟', 'correct_answer' => 'مانشستر سيتي'],
        ['question_text' => 'من هو الهداف التاريخي لريال مدريد؟', 'correct_answer' => 'كريستيانو رونالدو'],
        ['question_text' => 'كم مرة فاز منتخب ألمانيا بكأس العالم؟', 'correct_answer' => '4'],
        ['question_text' => 'أي دولة استضافت كأس العالم 2010؟', 'correct_answer' => 'جنوب أفريقيا'],
        ['question_text' => 'من هو هداف الدوري الإسباني التاريخي؟', 'correct_answer' => 'ليونيل ميسي'],
        ['question_text' => 'من هو حارس المرمى الوحيد الذي فاز بجائزة الكرة الذهبية؟', 'correct_answer' => 'ليف ياشين'],
        ['question_text' => 'أي منتخب فاز بأكبر عدد من كؤوس العالم؟', 'correct_answer' => 'البرازيل'],
        ['question_text' => 'من هو هداف كأس العالم 2018؟', 'correct_answer' => 'هاري كين'],
        ['question_text' => 'من هو مدرب الأرجنتين الفائز بكأس العالم 2022؟', 'correct_answer' => 'ليونيل سكالوني'],
        ['question_text' => 'أي نادٍ لعب له ليونيل ميسي قبل الانتقال إلى إنتر ميامي؟', 'correct_answer' => 'باريس سان جيرمان'],
        ['question_text' => 'من هو أفضل هداف في تاريخ دوري أبطال أوروبا؟', 'correct_answer' => 'كريستيانو رونالدو'],
        ['question_text' => 'كم جائزة كرة ذهبية فاز بها ليونيل ميسي؟', 'correct_answer' => '8'],
        ['question_text' => 'أي دولة استضافت كأس العالم 2014؟', 'correct_answer' => 'البرازيل'],
        ['question_text' => 'من هو هداف كأس العالم 2022؟', 'correct_answer' => 'كيليان مبابي'],
        ['question_text' => 'أي نادٍ فاز بدوري أبطال أوروبا 2021؟', 'correct_answer' => 'تشيلسي'],
        ['question_text' => 'كم مرة فاز منتخب فرنسا بكأس العالم؟', 'correct_answer' => '2'],
        ['question_text' => 'أي دولة فازت ببطولة كأس آسيا 2019؟', 'correct_answer' => 'قطر'],
        ['question_text' => 'من هو أكثر لاعب ظهوراً في تاريخ كأس العالم؟', 'correct_answer' => 'ليونيل ميسي'],
        ['question_text' => 'في أي عام توفي الأسطورة دييغو مارادونا؟', 'correct_answer' => '2020'],
        ['question_text' => 'من هو اللاعب الملقب بـ "الملك"؟', 'correct_answer' => 'بيليه'],
        ['question_text' => 'أي نادٍ يُلقب بـ "اليوفي"؟', 'correct_answer' => 'يوفنتوس'],
        ['question_text' => 'من هو أول لاعب يسجل 100 هدف في دوري أبطال أوروبا؟', 'correct_answer' => 'كريستيانو رونالدو'],
        ['question_text' => 'كم مرة فاز ريال مدريد بدوري أبطال أوروبا حتى 2024؟', 'correct_answer' => '15'],
        ['question_text' => 'أي دولة فازت بكأس أمم أفريقيا 2021؟', 'correct_answer' => 'السنغال'],
        ['question_text' => 'من هو الهداف التاريخي لكأس أمم أفريقيا؟', 'correct_answer' => 'صمويل إيتو'],
        ['question_text' => 'في أي عام فازت مصر بكأس أمم أفريقيا آخر مرة؟', 'correct_answer' => '2010'],
        ['question_text' => 'من هو أفضل لاعب في العالم 2023 حسب الفيفا؟', 'correct_answer' => 'ليونيل ميسي'],
        ['question_text' => 'من هو أول لاعب عربي يفوز بلقب هداف دوري أبطال أوروبا؟', 'correct_answer' => 'محمد صلاح'],
        ['question_text' => 'أي نادٍ فاز بالدوري الإنجليزي الممتاز موسم 2023-2024؟', 'correct_answer' => 'مانشستر سيتي'],
        ['question_text' => 'من هو الهداف التاريخي لبرشلونة؟', 'correct_answer' => 'ليونيل ميسي'],
        ['question_text' => 'كم هدفاً سجل كريستيانو رونالدو في مسيرته الاحترافية؟', 'correct_answer' => 'أكثر من 850'],
        ['question_text' => 'أي دولة استضافت كأس العالم 2006؟', 'correct_answer' => 'ألمانيا'],
        ['question_text' => 'من هو اللاعب الملقب بـ "الفتى الذهبي"؟', 'correct_answer' => 'واين روني'],
        ['question_text' => 'ما هو النادي الذي بدأ فيه كريستيانو رونالدو مسيرته الاحترافية؟', 'correct_answer' => 'سبورتينغ لشبونة'],
        ['question_text' => 'أي فريق فاز بأول نسخة من الدوري الإنجليزي الممتاز؟', 'correct_answer' => 'مانشستر يونايتد'],
    ];

    private array $gotQuestions = [
        ['question_text' => 'ما اسم القارة التي تدور فيها أحداث Game of Thrones؟', 'correct_answer' => 'ويستروس'],
        ['question_text' => 'ما اسم الجدار الجليدي العظيم في شمال ويستروس؟', 'correct_answer' => 'الجدار'],
        ['question_text' => 'من هو مؤسس سلالة تارغاريان؟', 'correct_answer' => 'إيغون الفاتح'],
        ['question_text' => 'ما اسم سيف جون سنو المصنوع من الفولاذ الفاليري؟', 'correct_answer' => 'لونغكلاو'],
        ['question_text' => 'من هي والدة تنانين داينيريس؟', 'correct_answer' => 'رايلا تارغاريان'],
        ['question_text' => 'ما هو الاسم الحقيقي لجون سنو؟', 'correct_answer' => 'إيغار تارغاريان'],
        ['question_text' => 'كم عدد عروش الممالك السبع؟', 'correct_answer' => '7'],
        ['question_text' => 'من هو الابن الأكبر لتايون لانستر؟', 'correct_answer' => 'جيمي لانستر'],
        ['question_text' => 'ما اسم زوجة نيد ستارك؟', 'correct_answer' => 'كاتلين ستارك'],
        ['question_text' => 'من هو ملك الليل؟', 'correct_answer' => 'ذا نايت كينغ'],
        ['question_text' => 'ما اسم عائلة تارغاريان؟', 'correct_answer' => 'بيت أوف تارغاريان'],
        ['question_text' => 'من هو مؤسس الحرس الليلي؟', 'correct_answer' => 'براندون ستارك'],
        ['question_text' => 'أين يقع عرش الحديد؟', 'correct_answer' => 'كينغز لاندينغ'],
        ['question_text' => 'ما اسم القصر الملكي في كينغز لاندينغ؟', 'correct_answer' => 'الريد كيب'],
        ['question_text' => 'من الذي قتل نيد ستارك؟', 'correct_answer' => 'جوفري باراثيون'],
        ['question_text' => 'ما اسم التنين الأسود الكبير لداينيريس؟', 'correct_answer' => 'دروغو'],
        ['question_text' => 'كم عدد مواسم Game of Thrones؟', 'correct_answer' => '8'],
        ['question_text' => 'من هي حاكمة دراغونستون بعد وفاة ستانيس؟', 'correct_answer' => 'داينيريس تارغاريان'],
        ['question_text' => 'ما هو شعار عائلة ستارك؟', 'correct_answer' => 'الذئب الرهيب'],
        ['question_text' => 'ما هو شعار عائلة لانستر؟', 'correct_answer' => 'الأسد الذهبي'],
        ['question_text' => 'من هو الذي قتل جوفري باراثيون؟', 'correct_answer' => 'أولينا تيريل'],
        ['question_text' => 'ما اسم زعيمة الدوثراكي؟', 'correct_answer' => 'داينيريس تارغاريان'],
        ['question_text' => 'من هو الثعلب الصغير (الغراب ذو الثلاث عيون)؟', 'correct_answer' => 'بران ستارك'],
        ['question_text' => 'ما اسم ملك الشمال بعد روب ستارك؟', 'correct_answer' => 'جون سنو'],
    ];

    private array $dexterQuestions = [
        ['question_text' => 'ما اسم بطل مسلسل Dexter؟', 'correct_answer' => 'دكستر مورغان'],
        ['question_text' => 'ما هي وظيفة دكستر مورغان؟', 'correct_answer' => 'محلل طبي لرواسب الدماء'],
        ['question_text' => 'ما اسم أخت دكستر؟', 'correct_answer' => 'ديبورا مورغان'],
        ['question_text' => 'ما هو اسم والد دكستر بالتبني؟', 'correct_answer' => 'هاري مورغان'],
        ['question_text' => 'ماذا يسمى القانون الذي يعيش به دكستر؟', 'correct_answer' => 'قانون هاري'],
        ['question_text' => 'في أي مدينة تدور أحداث المسلسل؟', 'correct_answer' => 'ميامي'],
        ['question_text' => 'ما اسم زوجة دكستر؟', 'correct_answer' => 'ريتا بينيت'],
        ['question_text' => 'أين يلقي دكستر جثث ضحاياه؟', 'correct_answer' => 'قاع المحيط'],
        ['question_text' => 'ما اسم ابن دكستر؟', 'correct_answer' => 'هاريسون مورغان'],
        ['question_text' => 'من هو الخصم الرئيسي في الموسم الأول؟', 'correct_answer' => 'ذا آيس تراك كيلر'],
        ['question_text' => 'ما اسم شرطي ميامي الذي يشك بدكستر؟', 'correct_answer' => 'جيمس دووكس'],
        ['question_text' => 'ما اسم مدير دكستر في العمل؟', 'correct_answer' => 'فينس ماسوكا'],
        ['question_text' => 'كم عدد مواسم المسلسل الأصلية؟', 'correct_answer' => '8'],
        ['question_text' => 'ما اسم صديق دكستر المقرب في العمل؟', 'correct_answer' => 'أنجيل باتيستا'],
        ['question_text' => 'ما هو اسم المسلسل الفرعي التكميلي؟', 'correct_answer' => 'Dexter: New Blood'],
        ['question_text' => 'من هي والدة دكستر البيولوجية؟', 'correct_answer' => 'لورا موزر'],
        ['question_text' => 'ما هي الحرفة التي يتقنها دكستر؟', 'correct_answer' => 'الجراحة'],
        ['question_text' => 'من هو أول شخص يقتله دكستر في المسلسل؟', 'correct_answer' => 'ضحية في اليوم الأول للعاصفة'],
        ['question_text' => 'ما اسم أخت دكستر البيولوجية؟', 'correct_answer' => 'ديبورا مورغان'],
        ['question_text' => 'كيف يموت دكستر في نهاية المسلسل الأصلي؟', 'correct_answer' => 'يعيش منعزلاً في الغابة'],
    ];

    private array $thePitQuestions = [
        ['question_text' => 'ما اسم بطل مسلسل الحفرة؟', 'correct_answer' => 'ياماتش كوشوفالي'],
        ['question_text' => 'ما اسم والد ياماتش في المسلسل؟', 'correct_answer' => 'إدريس كوشوفالي'],
        ['question_text' => 'من هو أكبر أبناء إدريس كوشوفالي؟', 'correct_answer' => 'إيدرين','check_answer' => 'إيدرين كوشوفالي'],
        ['question_text' => 'ما اسم منطقة الحفرة بالتركي؟', 'correct_answer' => 'Çukur'],
        ['question_text' => 'ما هو حي الحفرة المعروف به؟', 'correct_answer' => 'حي الحفرة'],
        ['question_text' => 'من هي حبيبة ياماتش في المسلسل؟', 'correct_answer' => 'إيبيك','check_answer' => 'إيبيك يلماز'],
        ['question_text' => 'من هو الخصم الرئيسي في الموسم الأول؟', 'correct_answer' => 'جوجو','check_answer' => 'جودات'],
        ['question_text' => 'ما اسم شقيق ياماتش الأكبر؟', 'correct_answer' => 'سالم كوشوفالي'],
        ['question_text' => 'من هو حارس الحفرة الأمين؟', 'correct_answer' => 'جيهانغير','check_answer' => 'جيهان غير'],
        ['question_text' => 'كم عدد مواسم مسلسل الحفرة؟', 'correct_answer' => '4'],
        ['question_text' => 'من هي والدة ياماتش؟', 'correct_answer' => 'سلطان كوشوفالي'],
        ['question_text' => 'ما اسم الشرير الذي يهدد الحفرة في الموسم الرابع؟', 'correct_answer' => 'كولشان','check_answer' => 'كولشان باشي'],
        ['question_text' => 'من هو صديق ياماتش المفضل؟', 'correct_answer' => 'تاس','check_answer' => 'تاس كالا'],
        ['question_text' => 'ما هو أصل عائلة كوشوفالي؟', 'correct_answer' => 'ألباني','check_answer' => 'أصول ألبانية'],
        ['question_text' => 'من هو ابن إدريس الذي يخونه؟', 'correct_answer' => 'كهرمان كوشوفالي'],
        ['question_text' => 'من هي بطلة المسلسل الأنثى الرئيسية؟', 'correct_answer' => 'إيبيك','check_answer' => 'إيبيك يلماز'],
        ['question_text' => 'ما معنى اسم ياماتش؟', 'correct_answer' => 'البطل الشجاع'],
        ['question_text' => 'من هو سيد الحفرة بعد إدريس؟', 'correct_answer' => 'ياماتش كوشوفالي'],
        ['question_text' => 'ما اسم أخ ياماتش الصغير؟', 'correct_answer' => 'أكار','check_answer' => 'أكار كوشوفالي'],
        ['question_text' => 'أين تقع الحفرة في المسلسل؟', 'correct_answer' => 'في إسطنبول'],
    ];

    private array $vikingsQuestions = [
        ['question_text' => 'من هو بطل مسلسل Vikings؟', 'correct_answer' => 'راغنار لوثبروك'],
        ['question_text' => 'ما اسم زوجة راغنار الأولى؟', 'correct_answer' => 'لاغيرتا'],
        ['question_text' => 'من هو شقيق راغنار؟', 'correct_answer' => 'رولو'],
        ['question_text' => 'ما اسم ابن راغنار الأكبر؟', 'correct_answer' => 'بيورن أيرونسايد'],
        ['question_text' => 'أي بلد يهاجمه الفايكنغ أولاً؟', 'correct_answer' => 'إنجلترا'],
        ['question_text' => 'ما اسم الراهب الذي يصبح صديقاً لراغنار؟', 'correct_answer' => 'أثيلستان'],
        ['question_text' => 'ما هو اسم إله الفايكنغ الرئيسي؟', 'correct_answer' => 'أودين'],
        ['question_text' => 'ما نوع السفينة التي استخدمها الفايكنغ؟', 'correct_answer' => 'سفينة لونغشيب'],
        ['question_text' => 'ما اسم ملك إنجلترا في المسلسل؟', 'correct_answer' => 'إيكبرت'],
        ['question_text' => 'من هو ابن راغنار الذي يصبح أشهر محارب؟', 'correct_answer' => 'بيورن أيرونسايد'],
        ['question_text' => 'أين ذهب راغنار في رحلته الأخيرة؟', 'correct_answer' => 'إنجلترا'],
        ['question_text' => 'ما اسم زوجة راغنار الثانية؟', 'correct_answer' => 'آسلوغ'],
        ['question_text' => 'كم عدد مواسم Vikings؟', 'correct_answer' => '6'],
        ['question_text' => 'من هو ملك الفايكنغ بعد راغنار؟', 'correct_answer' => 'بيورن أيرونسايد'],
        ['question_text' => 'ما اسم مستكشف الفايكنغ الشهير؟', 'correct_answer' => 'ليف إريكسون'],
        ['question_text' => 'أين استقر الفايكنغ في فرنسا؟', 'correct_answer' => 'نورماندي'],
        ['question_text' => 'من هي المحاربة الأنثى البارزة في المسلسل؟', 'correct_answer' => 'لاغيرتا'],
        ['question_text' => 'ما هو مفهوم الفالاهلا؟', 'correct_answer' => 'جنة المحاربين'],
        ['question_text' => 'من هو ابن راغنار الأعمى؟', 'correct_answer' => 'إيفار ذا بونليس'],
        ['question_text' => 'ما اسم زوجة بيورن؟', 'correct_answer' => 'غونيلدا','check_answer' => 'غونيلدا'],
    ];

    private array $breakingBadQuestions = [
        ['question_text' => 'ما اسم بطل مسلسل Breaking Bad؟', 'correct_answer' => 'والتر وايت'],
        ['question_text' => 'ما هي مهنة والتر وايت قبل مرضه؟', 'correct_answer' => 'مدرس كيمياء'],
        ['question_text' => 'ما اسم شريك والتر في تجارة المخدرات؟', 'correct_answer' => 'جيسي بينكمان'],
        ['question_text' => 'ما هو الاسم الرمزي لوالتر وايت؟', 'correct_answer' => 'هايزنبرغ'],
        ['question_text' => 'ما نوع السرطان الذي أصاب والتر؟', 'correct_answer' => 'سرطان الرئة'],
        ['question_text' => 'في أي مدينة تدور أحداث المسلسل؟', 'correct_answer' => 'ألباكركي'],
        ['question_text' => 'ما اسم زوجة والتر؟', 'correct_answer' => 'سكايلر وايت'],
        ['question_text' => 'ما اسم صهر والتر (الشرطي)؟', 'correct_answer' => 'هانك شريدر'],
        ['question_text' => 'ما هو لون المادة المخدرة التي يصنعها والتر؟', 'correct_answer' => 'أزرق'],
        ['question_text' => 'كم عدد مواسم Breaking Bad؟', 'correct_answer' => '5'],
        ['question_text' => 'ما اسم المحامي المنحرف في المسلسل؟', 'correct_answer' => 'سول غودمان'],
        ['question_text' => 'من هو تاجر المخدرات الكبير الذي يتعامل معه والتر؟', 'correct_answer' => 'غوس فرينغ'],
        ['question_text' => 'ما اسم مطعم الوجبات السريعة الذي يمتلكه غوس؟', 'correct_answer' => 'لوس بولوس هيرمانوس'],
        ['question_text' => 'من هو خادم غوس فرينغ المخلص؟', 'correct_answer' => 'مايك إيرمانتراوت'],
        ['question_text' => 'كيف يموت هانك شريدر؟', 'correct_answer' => 'يُقتل بالرصاص'],
        ['question_text' => 'ما اسم ابن والتر وايت؟', 'correct_answer' => 'والتر وايت جونيور'],
        ['question_text' => 'كم يوماً استغرقه مسلسل Breaking Bad زمنياً؟', 'correct_answer' => 'سنتان'],
        ['question_text' => 'ما هو المسلسل التكميلي لـ Breaking Bad؟', 'correct_answer' => 'Better Call Saul'],
        ['question_text' => 'من هو الخصم الرئيسي في الموسم الأخير؟', 'correct_answer' => 'جاك ويلكر'],
        ['question_text' => 'ما هو لون قبعة وايزنبرغ المميزة؟', 'correct_answer' => 'أسود'],
    ];

    private array $strangerThingsQuestions = [
        ['question_text' => 'في أي بلدة تدور أحداث Stranger Things؟', 'correct_answer' => 'هوكينز'],
        ['question_text' => 'ما هو العالم الموازي في المسلسل؟', 'correct_answer' => 'العالم المقلوب'],
        ['question_text' => 'ما اسم الفتاة الخارقة في المسلسل؟', 'correct_answer' => 'إيلفن (إيل)'],
        ['question_text' => 'من هو صديق إيلفن المفضل؟', 'correct_answer' => 'مايك ويلر'],
        ['question_text' => 'ما هو المخلوق الرئيسي الشرير في العالم المقلوب؟', 'correct_answer' => 'ديموغورغون'],
        ['question_text' => 'ما اسم أخ مايك الأكبر؟', 'correct_answer' => 'نانسي ويلر'],
        ['question_text' => 'أين يعمل والد إيلفن الحقيقي؟', 'correct_answer' => 'مختبر هوكينز'],
        ['question_text' => 'ما هو الوحش الرئيسي في الموسم الرابع؟', 'correct_answer' => 'فيكنا'],
        ['question_text' => 'كم عدد مواسم Stranger Things حتى 2024؟', 'correct_answer' => '4'],
        ['question_text' => 'ما نوع القوى الخارقة التي تمتلكها إيل؟', 'correct_answer' => 'تحريك الأشياء بالعقل'],
        ['question_text' => 'من هو رئيس مختبر هوكينز؟', 'correct_answer' => 'دكتور برينر'],
        ['question_text' => 'ما اسم والدة مايك؟', 'correct_answer' => 'كارين ويلر'],
        ['question_text' => 'من هو صديق مايك ذو الشعر الكثيف؟', 'correct_answer' => 'داستن هندرسون'],
        ['question_text' => 'ما هو الكائن الشرير في الموسم الثاني؟', 'correct_answer' => 'ميند فليير'],
        ['question_text' => 'ما هي هواية داستن المفضلة؟', 'correct_answer' => 'العاب الفيديو'],
        ['question_text' => 'ما اسم صديقة ستيف هارينغتون؟', 'correct_answer' => 'نانسي ويلر'],
        ['question_text' => 'في أي عقد زمني تدور أحداث المسلسل؟', 'correct_answer' => 'الثمانينات'],
        ['question_text' => 'ما هي لعبة الأركيد المفضلة للأبطال؟', 'correct_answer' => 'Dragon\'s Lair'],
        ['question_text' => 'ما اسم أخ نانسي الأصغر؟', 'correct_answer' => 'مايك ويلر'],
        ['question_text' => 'من هو صديق مايك البدين؟', 'correct_answer' => 'لوكاس سنكلير'],
    ];

    private array $prisonBreakQuestions = [
        ['question_text' => 'ما اسم بطل مسلسل Prison Break؟', 'correct_answer' => 'مايكل سكوفيلد'],
        ['question_text' => 'ما اسم أخ مايكل المسجون؟', 'correct_answer' => 'لينكولن باروز'],
        ['question_text' => 'ما هي مهنة مايكل سكوفيلد؟', 'correct_answer' => 'مهندس معماري'],
        ['question_text' => 'ما اسم السجن الذي دخل إليه مايكل؟', 'correct_answer' => 'فوكس ريفر'],
        ['question_text' => 'ماذا رسم مايكل على جسده كوشم؟', 'correct_answer' => 'مخطط السجن'],
        ['question_text' => 'من هي طبيبة السجن؟', 'correct_answer' => 'سارة تانكريدي'],
        ['question_text' => 'ما هو اسم زعيم العصابة داخل السجن؟', 'correct_answer' => 'جون أبروزي'],
        ['question_text' => 'من هو حارس السجن الشرير؟', 'correct_answer' => 'براد بيليك'],
        ['question_text' => 'كم عدد مواسم Prison Break؟', 'correct_answer' => '5'],
        ['question_text' => 'كيف اتصل مايكل بلينكولن داخل السجن؟', 'correct_answer' => 'عبر ثقب في الحائط'],
        ['question_text' => 'من هو مدير سجن فوكس ريفر؟', 'correct_answer' => 'هنري بوب'],
        ['question_text' => 'ما اسم صديق مايكل الذي يساعده من الخارج؟', 'correct_answer' => 'فيرنون ماهاون'],
        ['question_text' => 'أي دولة يهربون إليها في الموسم الأول؟', 'correct_answer' => 'بنما'],
        ['question_text' => 'ما اسم زوجة لينكولن السابقة؟', 'correct_answer' => 'ليزا باروز'],
        ['question_text' => 'من هي وكيلة النيابة التي تلاحقهم؟', 'correct_answer' => 'ألكسندرا ماهون'],
        ['question_text' => 'لماذا تم سجن لينكولن؟', 'correct_answer' => 'بسبب جريمة لم يرتكبها'],
        ['question_text' => 'ما اسم أخ مايكل من جهة الأم؟', 'correct_answer' => 'لينكولن باروز'],
        ['question_text' => 'كيف يخرج السجناء من السجن في النهاية؟', 'correct_answer' => 'عبر نفق الصرف الصحي'],
        ['question_text' => 'ما هو مرض مايكل في نهاية المسلسل؟', 'correct_answer' => 'ورم في الدماغ'],
        ['question_text' => 'من هو الخصم الرئيسي للموسم الأول؟', 'correct_answer' => 'نائبة الرئيس رينولدز'],
    ];

    private array $casaDePapelQuestions = [
        ['question_text' => 'ما اسم المسلسل الأصلي La Casa de Papel بالعربية؟', 'correct_answer' => 'بيت من ورق'],
        ['question_text' => 'ما اسم العقل المدبر للسرقة؟', 'correct_answer' => 'الأستاذ (البروفيسور)'],
        ['question_text' => 'ما هو الاسم الحقيقي للأستاذ؟', 'correct_answer' => 'سيرجيو ماركينا'],
        ['question_text' => 'ما اسم الفتاة التي تقود فريق السرقة؟', 'correct_answer' => 'طوكيو'],
        ['question_text' => 'أي بنك يتم سرقته في الجزء الأول؟', 'correct_answer' => 'بنك إسبانيا'],
        ['question_text' => 'ما لون بزة فريق السرقة؟', 'correct_answer' => 'أحمر'],
        ['question_text' => 'ما اسم المفاوضة من طرف الشرطة؟', 'correct_answer' => 'راكيل موريلو'],
        ['question_text' => 'كم عدد مواسم La Casa de Papel؟', 'correct_answer' => '5'],
        ['question_text' => 'ما هي الأسماء الحركية للفريق مستوحاة من؟', 'correct_answer' => 'مدن عالمية'],
        ['question_text' => 'ما هو الاسم الحركي للشخصية التي تحب الموسيقى؟', 'correct_answer' => 'برلين'],
        ['question_text' => 'من هو الأخ الأصغر للأستاذ؟', 'correct_answer' => 'برلين (أندريس)'],
        ['question_text' => 'ما اسم المحققة التي تقع في حب الأستاذ؟', 'correct_answer' => 'راكيل موريلو'],
        ['question_text' => 'من هو الشخصية التي تموت في نهاية الجزء الثاني؟', 'correct_answer' => 'برلين'],
        ['question_text' => 'ما الأغنية التي اشتهر بها المسلسل؟', 'correct_answer' => 'My Life Is Going On'],
        ['question_text' => 'ما اسم طابع الطباعة الذي يطبعه البروفيسور؟', 'correct_answer' => 'لا يطبع شيئاً في البنك'],
        ['question_text' => 'أي دولة تقع فيها أحداث المسلسل؟', 'correct_answer' => 'إسبانيا'],
        ['question_text' => 'من هي الشخصية الملقبة بـ "نيروبي"؟', 'correct_answer' => 'أغاتا خيمينيز'],
        ['question_text' => 'ما هي خطة البروفيسور الأساسية؟', 'correct_answer' => 'طباعة النقود في دار السك'],
        ['question_text' => 'كم عدد أفراد فريق السرقة الأساسي؟', 'correct_answer' => '8'],
        ['question_text' => 'من هو والد البروفيسور؟', 'correct_answer' => 'لصوص أموال'],
    ];

    public function up(): void
    {
        $footballId = 1;

        $categoryIds = [];

        foreach ($this->seriesCategories as $cat) {
            $existing = Category::where('slug', $cat['slug'])->first();
            if ($existing) {
                $categoryIds[$cat['slug']] = $existing->id;
            } else {
                $categoryIds[$cat['slug']] = DB::table('categories')->insertGetId([
                    'name' => $cat['name'],
                    'slug' => $cat['slug'],
                    'description' => null,
                    'is_active' => 1,
                    'is_featured' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $now = now();

        $footballInserts = array_map(fn($q) => [
            'question_text' => $q['question_text'],
            'correct_answer' => $q['correct_answer'],
            'category_id' => $footballId,
            'difficulty' => 'medium',
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->footballQuestions);

        DB::table('bluff_questions')->insert($footballInserts);
        echo "Inserted " . count($footballInserts) . " football questions.\n";

        $categoryMapping = [
            'got' => 'game-of-thrones',
            'dexter' => 'dexter',
            'thePit' => 'the-pit',
            'vikings' => 'vikings',
            'breakingBad' => 'breaking-bad',
            'strangerThings' => 'stranger-things',
            'prisonBreak' => 'prison-break',
            'casaDePapel' => 'la-casa-de-papel',
        ];

        $insertMap = [
            'got' => $this->gotQuestions,
            'dexter' => $this->dexterQuestions,
            'thePit' => $this->thePitQuestions,
            'vikings' => $this->vikingsQuestions,
            'breakingBad' => $this->breakingBadQuestions,
            'strangerThings' => $this->strangerThingsQuestions,
            'prisonBreak' => $this->prisonBreakQuestions,
            'casaDePapel' => $this->casaDePapelQuestions,
        ];

        foreach ($insertMap as $key => $questions) {
            $slug = $categoryMapping[$key];
            $catId = $categoryIds[$slug];
            $inserts = array_map(fn($q) => [
                'question_text' => $q['question_text'],
                'correct_answer' => $q['check_answer'] ?? $q['correct_answer'],
                'category_id' => $catId,
                'difficulty' => 'medium',
                'created_at' => $now,
                'updated_at' => $now,
            ], $questions);
            DB::table('bluff_questions')->insert($inserts);
            echo "Inserted " . count($inserts) . " questions for {$slug}.\n";
        }
    }

    public function down(): void
    {
        $slugs = array_column($this->seriesCategories, 'slug');
        $categoryIds = Category::whereIn('slug', $slugs)->pluck('id');

        if ($categoryIds->isNotEmpty()) {
            BluffQuestion::whereIn('category_id', $categoryIds)->delete();
            Category::whereIn('id', $categoryIds)->delete();
        }

        BluffQuestion::where('category_id', 1)
            ->whereIn('question_text', array_column($this->footballQuestions, 'question_text'))
            ->delete();
    }
};
