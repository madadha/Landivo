<?php

namespace App;

enum LandingSectionType: string
{
    case Hero = 'hero';
    case ProductGallery = 'product_gallery';
    case ProductShowcase = 'product_showcase';
    case Gallery = 'gallery';
    case Slider = 'slider';
    case Features = 'features';
    case Countdown = 'countdown';
    case Testimonials = 'testimonials';
    case Faq = 'faq';
    case LimitedStock = 'limited_stock';
    case ViewerCounter = 'viewer_counter';
    case OrderForm = 'order_form';
    case Whatsapp = 'whatsapp';
    case Footer = 'footer';
    case Video = 'video';
    case SocialMedia = 'social_media';

    public function label(): string
    {
        $customLabels = [
            self::Slider->value => app()->getLocale() === 'ar' ? 'سلايدر الصور' : 'Image Slider',
            self::Faq->value => app()->getLocale() === 'ar' ? 'الأكورديون والأسئلة' : 'Accordion & FAQ',
            self::LimitedStock->value => app()->getLocale() === 'ar' ? 'عداد الكمية المحدودة' : 'Limited Stock Counter',
            self::ViewerCounter->value => app()->getLocale() === 'ar' ? 'عداد المتصفحين الآن' : 'Live Viewers Counter',
        ];

        if (isset($customLabels[$this->value])) {
            return $customLabels[$this->value];
        }

        if ($this === self::ProductShowcase) {
            return app()->getLocale() === 'ar' ? 'بطاقات المنتجات' : 'Product Cards';
        }

        return __('landivo.sections.'.$this->value);
    }
}
