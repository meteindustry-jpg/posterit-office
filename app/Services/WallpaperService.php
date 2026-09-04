<?php

namespace App\Services;

class WallpaperService
{
    /**
     * Curated collection of breathtaking, mind-refreshing 4K serene nature wallpapers.
     */
    public static array $curatedWallpapers = [
        'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=2070&q=80', // Yosemite Valley Stream
        'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=2074&q=80', // Misty Morning Hills
        'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=2071&q=80', // Sunbeams in Calm Forest
        'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=2073&q=80', // Serene Calm Ocean Beach
        'https://images.unsplash.com/photo-1472214103451-9374bd1c798e?auto=format&fit=crop&w=2070&q=80', // Green Meadow & Soft Clouds
        'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=2070&q=80', // Gentle Sunrise Mountains
        'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=2070&q=80', // Crystal Reflection Lake
        'https://images.unsplash.com/photo-1426604966848-d7adac402bff?auto=format&fit=crop&w=2070&q=80', // Golden Hour Mountain Pass
        'https://images.unsplash.com/photo-1518495973542-4542c06a5843?auto=format&fit=crop&w=2070&q=80', // Warm Sunlight through Trees
        'https://images.unsplash.com/photo-1493246507139-91e8fad9978e?auto=format&fit=crop&w=2070&q=80', // Emerald Alpine Lake
        'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=2070&q=80', // Starry Twilight Peak
        'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=2070&q=80', // Pastel Sunset Cloudscape
        'https://images.unsplash.com/photo-1473448912268-2022ce9509d8?auto=format&fit=crop&w=2041&q=80', // Autumn Warm Pine Forest
        'https://images.unsplash.com/photo-1433086966358-54859d0ed716?auto=format&fit=crop&w=2074&q=80', // Peaceful Waterfalls & Moss
        'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=2074&q=80', // Warm Glow Mountain Trail
        'https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&w=2070&q=80', // Lush Green Foliage & Rain
        'https://images.unsplash.com/photo-1475924156734-496f6cac6ec1?auto=format&fit=crop&w=2070&q=80', // Peaceful Ocean Dawn
        'https://images.unsplash.com/photo-1508873696983-2df57046475b?auto=format&fit=crop&w=2073&q=80', // Nordic Fjord Silence
        'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=2070&q=80', // Tall Redwoods Sunlight
        'https://images.unsplash.com/photo-1498887960847-2a5e46312788?auto=format&fit=crop&w=2069&q=80', // Soft Pastel Dunes
        'https://images.unsplash.com/photo-1511497584788-87676104235f?auto=format&fit=crop&w=2070&q=80', // Evergreen Misty Morning
        'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=2070&q=80', // Lake House Reflection
        'https://images.unsplash.com/photo-1439853949127-fa647821eba0?auto=format&fit=crop&w=2070&q=80', // Serene Glacial Valley
        'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=2070&q=80', // Snow capped peaks & sun
        'https://images.unsplash.com/photo-1465146344425-f00d5f5c8f07?auto=format&fit=crop&w=2076&q=80', // Wildflowers & Calm Meadow
        'https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?auto=format&fit=crop&w=2070&q=80', // Forest Sunbeams
        'https://images.unsplash.com/photo-1516214104703-d870798883c5?auto=format&fit=crop&w=2070&q=80', // Morning Lake Mist
        'https://images.unsplash.com/photo-1518837695005-2083093ee35b?auto=format&fit=crop&w=2070&q=80', // Calming Blue Wave Horizon
        'https://images.unsplash.com/photo-1470240731273-7821a6eeb6bd?auto=format&fit=crop&w=2070&q=80', // Spring Blossom Hill
        'https://images.unsplash.com/photo-1495616811223-4d98c6e9c869?auto=format&fit=crop&w=2070&q=80', // Golden Sunset Reflections
    ];

    /**
     * Get the dynamic daily wallpaper URL.
     */
    public static function getDailyWallpaper(): string
    {
        $dayIndex = ((int) now()->format('j') - 1) % count(self::$curatedWallpapers);

        return self::$curatedWallpapers[$dayIndex] ?? asset('images/mind_refreshing_bg.jpg');
    }

    /**
     * Get all curated wallpapers for client-side rotation.
     */
    public static function getAllWallpapers(): array
    {
        return self::$curatedWallpapers;
    }
}
