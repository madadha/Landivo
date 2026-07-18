<?php

namespace App;

enum LandingSectionType: string
{
    case Hero = 'hero';
    case ProductGallery = 'product_gallery';
    case ProductShowcase = 'product_showcase';
    case Gallery = 'gallery';
    case Features = 'features';
    case Countdown = 'countdown';
    case Testimonials = 'testimonials';
    case Faq = 'faq';
    case OrderForm = 'order_form';
    case Whatsapp = 'whatsapp';
    case Footer = 'footer';
    case Video = 'video';
    case SocialMedia = 'social_media';

    public function label(): string
    {
        if ($this === self::ProductShowcase) {
            return app()->getLocale() === 'ar' ? 'بطاقات المنتجات' : 'Product Cards';
        }

        return __('landivo.sections.'.$this->value);
    }
}
