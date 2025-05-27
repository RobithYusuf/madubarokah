<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ShopSettingsController extends Controller
{
    public function index()
    {
        $settings = $this->getShopSettings();
        return view('admin.settings.shop', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:100',
            'warehouse_city_id' => 'required|integer',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $settings = $request->only([
            'name',
            'tagline',
            'address',
            'phone',
            'email',
            'whatsapp',
            'instagram',
            'facebook',
            'warehouse_city_id'
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('shop', 'public');
            $settings['logo'] = $logoPath;
        }

        // Save to cache
        Cache::put('shop_settings', $settings, now()->addDays(30));

        // Update shop.php config file
        $configUpdated = $this->updateShopConfigFile($settings);

        if ($configUpdated) {
            return redirect()->route('admin.settings.shop')
                ->with('success', 'Pengaturan toko berhasil disimpan!');
        } else {
            // Config update failed, but cache was updated
            return redirect()->route('admin.settings.shop')
                ->with('warning', 'Pengaturan disimpan ke cache, tetapi gagal memperbarui file konfigurasi. Perubahan mungkin tidak permanen.');
        }
    }

    private function getShopSettings()
    {
        // Get from cache, fallback to config
        return Cache::get('shop_settings', config('shop'));
    }

    public static function getSetting($key, $default = null)
    {
        $settings = Cache::get('shop_settings', config('shop'));
        return data_get($settings, $key, $default);
    }

    private function updateShopConfigFile($settings)
    {
        try {
            $configPath = config_path('shop.php');
            
            // Get current config to preserve non-editable values
            $currentConfig = config('shop');
            
            // Update only the editable fields
            $updatedConfig = array_merge($currentConfig, [
                'name' => $settings['name'] ?? $currentConfig['name'],
                'tagline' => $settings['tagline'] ?? $currentConfig['tagline'],
                'address' => $settings['address'] ?? $currentConfig['address'],
                'phone' => $settings['phone'] ?? $currentConfig['phone'],
                'email' => $settings['email'] ?? $currentConfig['email'],
                'whatsapp' => $settings['whatsapp'] ?? $currentConfig['whatsapp'],
                'instagram' => $settings['instagram'] ?? $currentConfig['instagram'],
                'facebook' => $settings['facebook'] ?? $currentConfig['facebook'],
                'warehouse_city_id' => (int)($settings['warehouse_city_id'] ?? $currentConfig['warehouse_city_id']),
            ]);
            
            // Handle logo separately if provided
            if (isset($settings['logo']) && !empty($settings['logo'])) {
                $updatedConfig['logo'] = $settings['logo'];
            }
            
            // Generate the complete config file content
            $configContent = $this->generateConfigContent($updatedConfig);
            
            // Write the updated content back to the file
            File::put($configPath, $configContent);

            // Clear config cache to apply changes
            \Illuminate\Support\Facades\Artisan::call('config:clear');

            return true;
        } catch (\Exception $e) {
            // Log error but don't interrupt the process
            \Log::error('Failed to update shop config file: ' . $e->getMessage());
            return false;
        }
    }
    
    private function generateConfigContent($config)
    {
        $content = "<?php\n\nreturn [\n";
        $content .= "    // Informasi Toko\n";
        $content .= "    'name' => '" . addslashes($config['name']) . "',\n";
        $content .= "    'tagline' => '" . addslashes($config['tagline']) . "',\n";
        $content .= "    'address' => '" . addslashes($config['address']) . "',\n";
        $content .= "    'phone' => '" . addslashes($config['phone']) . "',\n";
        $content .= "    'email' => '" . addslashes($config['email']) . "',\n";
        $content .= "    'logo' => '" . addslashes($config['logo']) . "',\n\n";
        
        $content .= "    // Social Media\n";
        $content .= "    'whatsapp' => '" . addslashes($config['whatsapp'] ?? '') . "',\n";
        $content .= "    'instagram' => '" . addslashes($config['instagram'] ?? '') . "',\n";
        $content .= "    'facebook' => '" . addslashes($config['facebook'] ?? '') . "',\n\n";
        
        $content .= "    // Warehouse Location (Kudus)\n";
        $content .= "    'warehouse_city_id' => " . ($config['warehouse_city_id'] ?? 209) . ",\n";
        $content .= "    'warehouse_province_id' => " . ($config['warehouse_province_id'] ?? 10) . ",\n\n";
        
        // Preserve business hours and specialties if they exist
        if (isset($config['business_hours'])) {
            $content .= "    // Business Hours\n";
            $content .= "    'business_hours' => [\n";
            foreach ($config['business_hours'] as $key => $value) {
                $content .= "        '" . addslashes($key) . "' => '" . addslashes($value) . "',\n";
            }
            $content .= "    ],\n\n";
        }
        
        if (isset($config['specialties'])) {
            $content .= "    // Product Specialties\n";
            $content .= "    'specialties' => [\n";
            foreach ($config['specialties'] as $specialty) {
                $content .= "        '" . addslashes($specialty) . "',\n";
            }
            $content .= "    ]\n";
        }
        
        $content .= "];\n";
        
        return $content;
    }
}
