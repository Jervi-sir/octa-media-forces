<?php

namespace App\Helpers\v2_9;

use App\Models\ContactPlatform;

class OGMHelpers
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static $max_published_for_approval = 2;

    public static function OgmAccount($user)
    {
        // $wilaya = $user->wilaya;
        return [
            'id' => $user->id,
            'displayed_id' => $user->displayed_id,
            'full_name' => $user->full_name,
            'username' => $user->username,
            'phone_number' => $user->phone_number,
            'image' => $user->image,
            'is_approved' => $user->is_approved,
            // 'wilaya' => $user->wilaya_id ? [
            //     'id' => $wilaya->id,
            //     'name' => $wilaya->name,
            //     'number' => $wilaya->number,
            //     'number_text' => $wilaya->number_text,
            //     'en' => $wilaya->en,
            //     'ar' => $wilaya->ar,
            //     'fr' => $wilaya->fr,
            // ] : null,
        ];
    }

    public static function GenerateImageUrl($image_url)
    {
        $baseUrl = rtrim(env('PRODUCTION_URL'), '/');
        $prependBase = function ($url) use ($baseUrl) {
            if (empty($url)) {
                return $url;
            }
            if (preg_match('/^https?:\/\//i', $url)) {
                return $url;
            }
            return $baseUrl . '/' . ltrim($url, '/');
        };
        if (is_array($image_url)) {
            return array_map($prependBase, $image_url);
        }
        return $prependBase($image_url);
    }

    public static function normalizeImagePath($input)
    {
        $baseUrl = rtrim(env('PRODUCTION_URL'), '/');
        $normalize = function ($image) use ($baseUrl) {
            if (is_string($image) && !empty($image)) {
                return str_replace($baseUrl . '/storage/', 'storage/', $image);
            }
            return $image;
        };
        if (is_array($input)) {
            return array_map($normalize, $input);
        }
        return $normalize($input);
    }


    public static function DraftFormatter($draft)
    {
        return [
            'id' => $draft->id,
            'ogm_id' => $draft->ogm_id,
            'image_url' => self::GenerateImageUrl($draft->image_url),
            'created_at' => $draft->created_at,
        ];
    }

    public static function ProductFormat($product, $include = [])
    {
        $data = [
            'id' => $product->id,
            'images' => self::GenerateImageUrl($product->images),
            'price' => $product->price,
            'discount' => $product->discount,
            'discount_end_date' => $product->discount_end_date,
            'description' => $product->description,
            'category_id' => $product->category_id,
            'status' => $product->status,
            'posted_at' => $product->posted_at,
            'disappear_at' => $product->disappear_at,
            'status_id' => $product->status_id,
        ];
        // Include related data based on $include array
        if (in_array('os', $include) && $product->os)
            $data['os'] = self::OsFormat($product->os);

        if (in_array('category', $include) && $product->category)
            $data['category'] = self::CategoryFormat($product->category);

        if (in_array('sizes', $include) && $product->sizes) {
            $data['sizes'] = $product->sizes->map(fn($size) => self::SizeFormat($size));
        }

        if (in_array('genders', $include) && $product->genders) {
            $data['genders'] = $product->genders->map(fn($gender) => self::GenderFormat($gender));
        }

        if (in_array('tags', $include) && $product->tags) {
            $data['tags'] = $product->tags->map(fn($tag) => self::TagFormat($tag));
        }

        return $data;
    }


    public static function ProductPreview($product)
    {
        return [
            'id' => $product->id,
            'thumbnail' => self::GenerateImageUrl($product->images[0]),
            'nb_images' => count($product->images),
            'disappear_at' => $product->disappear_at
        ];
    }

    public static function OsFormat($os)
    {
        return [
            'id' => $os->id,
            'ogm_id' => $os->ogm_id,
            'wilaya' => [
                'id' => $os->wilaya->id,
                'name' => $os->wilaya->name,
                'number' => $os->wilaya->number,
                'number_text' => $os->wilaya->number_text,
            ],
            'store_name' => $os->store_name,
            'image' => $os->image,
            'phone_number' => $os->phone_number,
            'map_link' => $os->map_link,
            'bio' => $os->bio,
        ];
    }

    public static function ContactFormat($platform)
    {
        return [
            'id' => $platform->id,
            'os_id' => $platform->os_id,
            'link' => $platform->link,
            'contact_platforms_id' => $platform->contact_platforms_id,
            'platform' => $platform->contact_platform,
            // 'platform' => [
            //     'id' => $platform->contact_platform->id,
            //     'name' => $platform->contact_platform->name,
            //     'code_name' => $platform->contact_platform->code_name,
            //     // 'icon_svg' => $platform->contact_platform->icon_svg,
            //     // 'icon_url' => $platform->contact_platform->icon_url,
            // ],
            'created_at' => $platform->created_at,
            'updated_at' => $platform->updated_at,
        ];
    }

    public static function CategoryFormat($category)
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'en' => $category->en,
            'ar' => $category->ar,
            'fr' => $category->fr,
        ];
    }

    public static function GenderFormat($gender)
    {
        return [
            'id' => $gender->id,
            'gender' => $gender->gender,
        ];
    }


    public static function SizeFormat($size)
    {
        return [
            'id' => $size->id,
            'size' => $size->size,
        ];
    }

    public static function TagFormat($tag)
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
        ];
    }


    public static function WilayaFormat($wilaya)
    {
        return [
            'id' => $wilaya->id,
            'name' => $wilaya->name,
            'number' => $wilaya->number,
            'number_text' => $wilaya->number_text,
            'en' => $wilaya->en,
            'ar' => $wilaya->ar,
            'fr' => $wilaya->fr,
        ];
    }

    public static function GetPlatforms()
    {
        $platforms = ContactPlatform::all();
        return $platforms->map(fn($category) => [
            'id' => $category->id,
            'name' => $category->name,
            'code_name' => $category->code_name,
            // 'icon_url' => $category->icon_url,
            // 'icon_svg' => $category->icon_svg,
            // 'description' => $category->description,
        ]);
    }
}
