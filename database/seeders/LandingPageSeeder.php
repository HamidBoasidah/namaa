<?php

namespace Database\Seeders;

use App\Models\LandingPage;
use App\Models\LandingSection;
use App\Models\LandingSectionItem;
use Illuminate\Database\Seeder;

class LandingPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the main landing page
        $landingPage = LandingPage::create([
            'title' => 'كسب - الصفحة الرئيسية',
            'slug' => 'home',
            'is_active' => true,
            'meta_title' => 'كسب - منصة الاستشارات المالية والمحاسبية',
            'meta_description' => 'منصة تربط رواد الأعمال بمستشارين ماليين ومحاسبين معتمدين',
        ]);

        // Hero Section
        $heroSection = LandingSection::create([
            'landing_page_id' => $landingPage->id,
            'type' => 'hero',
            'title' => 'خُذ قراراتك بثقة مع خبراء مال وأعمال',
            'subtitle' => 'من تحليل القوائم المالية إلى دراسة الجدوى وخطط النمو، كل ما تحتاجه متاح في مكان واحد.',
            'order' => 0,
            'is_active' => true,
        ]);

        // Features Section
        $featuresSection = LandingSection::create([
            'landing_page_id' => $landingPage->id,
            'type' => 'features',
            'title' => 'لماذا تختار منصتنا؟',
            'subtitle' => 'نوفر لك كل ما تحتاجه للحصول على استشارات مالية ومحاسبية احترافية بسهولة وأمان',
            'order' => 1,
            'is_active' => true,
        ]);

        $features = [
            [
                'title' => 'ابحث عن المستشار المناسب لمشروعك',
                'description' => 'تصفح مئات المستشارين المعتمدين واختر الأنسب حسب تخصصك واحتياجات عملك',
                'icon' => 'search',
            ],
            [
                'title' => 'احجز جلسات استشارية عبر الإنترنت',
                'description' => 'جلسات مباشرة عبر الفيديو أو الصوت أو المحادثة النصية حسب راحتك',
                'icon' => 'video',
            ],
            [
                'title' => 'اطلب خدمات جاهزة ومتخصصة',
                'description' => 'دراسات جدوى، تحليل مالي، خطط أعمال، إعادة هيكلة الديون، وتقييم الأداء',
                'icon' => 'document',
            ],
            [
                'title' => 'تابع استشاراتك من مكان واحد',
                'description' => 'إدارة سهلة لكل مواعيدك، سجلات الجلسات، والتقارير المستلمة.',
                'icon' => 'folder',
            ],
            [
                'title' => 'مدفوعات آمنة وسياسة استرداد واضحة',
                'description' => 'تصفح مئات المستشارين المعتمدين واختر الأنسب حسب تخصصك واحتياجات عملك',
                'icon' => 'shield',
            ],
            [
                'title' => 'قيّم المستشار وشارك تجربتك',
                'description' => 'امنح المستشار تقييمك، وساعد باقي رواد الأعمال باختيارهم الأنسب لاحتياجاتهم.',
                'icon' => 'star',
            ],
        ];

        foreach ($features as $index => $feature) {
            LandingSectionItem::create([
                'landing_section_id' => $featuresSection->id,
                'title' => $feature['title'],
                'description' => $feature['description'],
                'icon' => $feature['icon'],
                'order' => $index,
                'is_active' => true,
            ]);
        }

        // How It Works Section
        $howItWorksSection = LandingSection::create([
            'landing_page_id' => $landingPage->id,
            'type' => 'how_it_works',
            'title' => 'ثلاث خطوات بسيطة',
            'subtitle' => 'للحصول على الاستشارة التي تحتاجها',
            'order' => 2,
            'is_active' => true,
        ]);

        $steps = [
            [
                'title' => 'تصميم المستشارين والخدمات',
                'description' => 'ابحث عن المستشارين حسب التخصص والتقييمات واختر الخدمة المناسبة',
            ],
            [
                'title' => 'اختر الوقت والطريقة وادفع بأمان',
                'description' => 'حدد موعد الجلسة ونوع الاستشارة (فيديو/صوت/نص)، وأتمم الدفع الآمن',
            ],
            [
                'title' => 'التق بالمستشار واستلم التقرير',
                'description' => 'احضر الجلسة الاستشارية، استلم التقارير والمخرجات، ثم قيّم الخدمة',
            ],
        ];

        foreach ($steps as $index => $step) {
            LandingSectionItem::create([
                'landing_section_id' => $howItWorksSection->id,
                'title' => $step['title'],
                'description' => $step['description'],
                'order' => $index,
                'is_active' => true,
            ]);
        }

        // Services Section
        $servicesSection = LandingSection::create([
            'landing_page_id' => $landingPage->id,
            'type' => 'services',
            'title' => 'خدمات متكاملة تُتقي مشروعك',
            'subtitle' => 'مجموعة متنوعة من الخدمات المالية والمحاسبية المصممة لدعم نمو أعمالك',
            'order' => 3,
            'is_active' => true,
        ]);

        $services = [
            [
                'title' => 'دراسة جدوى',
                'description' => 'دراسة حجم السوق، أنواع العملاء، سلوكهم، والمنافسين لاكتشاف فرص النمو والتهديدات.',
                'background_color' => 'bg-gradient-to-br from-[#E0F2FE] to-[#BAE6FD]',
                'icon' => '💡',
            ],
            [
                'title' => 'تحليل سوق',
                'description' => 'دراسة حجم السوق، أنواع المصاف، سلوكهم، والمنافسين لاكتشاف فرص النمو والتهديدات.',
                'background_color' => 'bg-gradient-to-br from-[#FCE7F3] to-[#FBCFE8]',
                'icon' => '🔍',
            ],
            [
                'title' => 'تحليل مالي',
                'description' => 'تقدير التكاليف والإيرادات والأرباح المتوقعة للمشروع، وحساب مؤشرات مثل نقطة التعادل والعائد على الاستثمار.',
                'background_color' => 'bg-gradient-to-br from-[#FFE4E6] to-[#FECDD3]',
                'icon' => '📊',
            ],
            [
                'title' => 'خطة عمل',
                'description' => 'وثيقة تو توضيح أهداف المشروع، وخطوات التشغيل، والموارد المطلوبة، والخطة المالية والتسويقية لفترة زمنية محددة.',
                'background_color' => 'bg-gradient-to-br from-[#DBEAFE] to-[#BFDBFE]',
                'icon' => '📋',
            ],
            [
                'title' => 'إدارة استثمار',
                'description' => 'مراقبة وتنظيم قرارات الاستثمار من اختيار الفرص وتوزيع رأس المال حتى مراقبة الأداء وتقليل المخاطر.',
                'background_color' => 'bg-gradient-to-br from-[#F3E8FF] to-[#E9D5FF]',
                'icon' => '💼',
            ],
            [
                'title' => 'تقييم أعمال',
                'description' => 'فحص أداء ونمو ومردودية الشركة لتحديد قيمتها السوقية أو جاجزتها للاستثمار أو البيع.',
                'background_color' => 'bg-gradient-to-br from-[#FEF3C7] to-[#FDE68A]',
                'icon' => '✅',
            ],
        ];

        foreach ($services as $index => $service) {
            LandingSectionItem::create([
                'landing_section_id' => $servicesSection->id,
                'title' => $service['title'],
                'description' => $service['description'],
                'background_color' => $service['background_color'],
                'icon' => $service['icon'],
                'order' => $index,
                'is_active' => true,
            ]);
        }

        // Mobile App Section
        $mobileAppSection = LandingSection::create([
            'landing_page_id' => $landingPage->id,
            'type' => 'mobile_app',
            'title' => 'انضم كمستشار واستفد من منصتنا',
            'subtitle' => 'وسع قاعدة عملائك وزد دخلك من خلال تقديم خدماتك الاستشارية عبر منصتنا الاحترافية',
            'order' => 4,
            'is_active' => true,
        ]);

        $mobileAppFeatures = [
            [
                'title' => 'أدر عملك من لوحة تحكم متنقلة',
                'description' => 'تابع حجوزاتك، عملائك، وأرباحك من هاتفك المحمول بسهولة',
                'background_color' => 'from-[#FDEBD0] to-[#FFF5E6]',
            ],
            [
                'title' => 'أضف خدماتك وحدد أسعارك',
                'description' => 'دراسات جدوى، تحليل مالي، خطط أعمال - قدم خدماتك بأسعارك الخاصة',
                'background_color' => 'from-[#FFF5E6] to-[#FFE8CC]',
            ],
            [
                'title' => 'وسع نطاق عملك',
                'description' => 'عروض متعددة، إشعارات فورية، محفظة إلكترونية، تقارير تفصيلية',
                'background_color' => 'from-[#FFE8CC] to-[#FFDDB3]',
            ],
        ];

        foreach ($mobileAppFeatures as $index => $feature) {
            LandingSectionItem::create([
                'landing_section_id' => $mobileAppSection->id,
                'title' => $feature['title'],
                'description' => $feature['description'],
                'background_color' => $feature['background_color'],
                'order' => $index,
                'is_active' => true,
            ]);
        }

        // Testimonials Section
        $testimonialsSection = LandingSection::create([
            'landing_page_id' => $landingPage->id,
            'type' => 'testimonials',
            'title' => 'ماذا يقول عملاؤنا',
            'subtitle' => 'آلاف رواد الأعمال وأصحاب المشاريع حققوا أهدافهم من خلال استشاراتنا',
            'order' => 5,
            'is_active' => true,
        ]);

        $testimonials = [
            [
                'title' => 'محمد السعيد',
                'subtitle' => 'مشغل أونجة البدنيل',
                'description' => '"المستشار كان محترفاً جداً وقدم لي نصائح عملية لتحسين التدفقات النقدية."',
            ],
            [
                'title' => 'فاطمة أحمد',
                'subtitle' => 'صاحبة مشروع تجاري',
                'description' => '"جلسة الاستشارة التجارية وضحت لي نقاط ضعف المنافسين وزودتني بأفكار جديدة للتطوير."',
            ],
            [
                'title' => 'خالد العتيبي',
                'subtitle' => 'رائد أعمال',
                'description' => '"حصلت على دراسة جدوى كاملة في وقت قياسي، ساعدتني كثيراً في إقناع المستثمرين."',
            ],
        ];

        foreach ($testimonials as $index => $testimonial) {
            LandingSectionItem::create([
                'landing_section_id' => $testimonialsSection->id,
                'title' => $testimonial['title'],
                'subtitle' => $testimonial['subtitle'],
                'description' => $testimonial['description'],
                'order' => $index,
                'is_active' => true,
            ]);
        }

        // FAQ Section
        $faqSection = LandingSection::create([
            'landing_page_id' => $landingPage->id,
            'type' => 'faq',
            'title' => 'استفسارات متكررة',
            'subtitle' => 'إجابات على أكثر الأسئلة شيوعاً حول خدماتنا',
            'order' => 6,
            'is_active' => true,
        ]);

        $faqs = [
            [
                'title' => 'كيف يمكنني حجز استشارة؟',
                'description' => 'يمكنك تصفح المستشارين المتاحين، اختيار المستشار المناسب، ثم تحديد نوع الاستشارة (فيديو/صوت/نص) والوقت المناسب لك. بعد إتمام الدفع الآمن، ستتلقى تأكيداً بالحجز وتفاصيل الاتصال.',
            ],
            [
                'title' => 'ما أنواع الخدمات المتاحة؟',
                'description' => 'نوفر استشارات مالية ومحاسبية متنوعة، بالإضافة إلى خدمات مثل دراسة الجدوى، تحليل السوق، التخطيط المالي، إعداد القوائم المالية، الاستشارات الضريبية، وتقييم المشاريع.',
            ],
            [
                'title' => 'كيف يتم فحص المستشارين؟',
                'description' => 'جميع المستشارين على منصتنا يخضعون لعملية تحقق صارمة تشمل التحقق من المؤهلات الأكاديمية والشهادات المهنية والخبرة العملية قبل الموافقة على انضمامهم للمنصة.',
            ],
            [
                'title' => 'كيف يتلقى المستشارون أرباحهم؟',
                'description' => 'يتلقى المستشارون أرباحهم بشكل دوري (أسبوعياً أو شهرياً) من خلال تحويل بنكي مباشر أو عبر المحفظة الإلكترونية المتاحة في التطبيق.',
            ],
            [
                'title' => 'هل الاستشارات سرية وآمنة؟',
                'description' => 'نعم، جميع الاستشارات والبيانات محمية بأحدث تقنيات التشفير. نلتزم بسياسات خصوصية صارمة لضمان حماية معلوماتك وبياناتك المالية.',
            ],
            [
                'title' => 'ماذا أفعل إذا واجهت مشكلة تقنية أثناء الاستشارة؟',
                'description' => 'يمكنك التواصل مع فريق الدعم الفني على مدار الساعة من خلال الشات المباشر في التطبيق أو عبر البريد الإلكتروني. سنقوم بحل أي مشكلة فوراً وإعادة جدولة الجلسة إذا لزم الأمر.',
            ],
        ];

        foreach ($faqs as $index => $faq) {
            LandingSectionItem::create([
                'landing_section_id' => $faqSection->id,
                'title' => $faq['title'],
                'description' => $faq['description'],
                'order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
