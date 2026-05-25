<?php
use App\Models\Setting;

$_wa  = Setting::get('main_whatsapp', '');
$_fb  = Setting::get('facebook_url', '');
$_ig  = Setting::get('instagram_url', '');

$_waHref = $_wa ? 'https://wa.me/' . htmlspecialchars($_wa, ENT_QUOTES, 'UTF-8') : '#';
$_fbHref = $_fb ? htmlspecialchars($_fb, ENT_QUOTES, 'UTF-8') : '#';
$_igHref = $_ig ? htmlspecialchars($_ig, ENT_QUOTES, 'UTF-8') : '#';

$basePath = rtrim(APP_BASE, '/');
?>
<div id="support-hub"
     class="fixed right-5 z-[999] flex flex-col gap-3
            bottom-24 md:bottom-6">

  <?php if ($_wa): ?>
  <div class="relative group">
    <span class="pointer-events-none absolute right-[3.5rem] top-1/2 -translate-y-1/2
                 hidden md:block
                 whitespace-nowrap rounded-lg border border-zinc-800 bg-black/95
                 px-2.5 py-1.5 text-[11px] font-semibold text-white
                 opacity-0 scale-90 transition-all duration-200
                 group-hover:opacity-100 group-hover:scale-100">
      Discuter sur WhatsApp
    </span>
    <a href="<?= $_waHref ?>" target="_blank" rel="noopener noreferrer"
       class="flex h-11 w-11 items-center justify-center rounded-full
              bg-emerald-500 text-black shadow-lg shadow-emerald-900/40
              hover:bg-emerald-400 hover:scale-110 active:scale-95
              transition-all duration-200"
       aria-label="Contacter KivuBoost sur WhatsApp">
      <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm5.824-3.414c1.657.984 3.284 1.503 4.908 1.504 5.485.002 9.948-4.457 9.951-9.94.002-2.656-1.031-5.152-2.905-7.028-1.875-1.877-4.371-2.91-7.006-2.91-5.48 0-9.94 4.457-9.943 9.942-.001 1.761.47 3.427 1.365 4.973l-1.011 3.693 3.769-.989zM18.062 14.88c-.3-.15-1.77-.874-2.043-.974-.275-.1-.475-.15-.675.15-.2.3-.77.974-.945 1.174-.175.2-.35.225-.65.075-1.516-.76-2.585-1.396-3.473-2.915-.235-.4-.235-.8-.061-.97.15-.15.3-.35.45-.524.15-.175.2-.3.3-.5.1-.2.05-.375-.025-.524-.075-.15-.675-1.625-.925-2.225-.244-.589-.493-.51-.675-.52l-.575-.01c-.2 0-.525.075-.8.375-.275.3-1.05 1.024-1.05 2.5 0 1.475 1.075 2.9 1.225 3.1.15.2 2.11 3.22 5.116 4.516.715.309 1.273.493 1.708.632.719.228 1.373.195 1.89.117.577-.087 1.77-.724 2.02-1.387.25-.662.25-1.237.175-1.387-.075-.15-.275-.25-.575-.4z"/>
      </svg>
    </a>
  </div>
  <?php endif; ?>

  <?php if ($_fb): ?>
  <div class="relative group">
    <span class="pointer-events-none absolute right-[3.5rem] top-1/2 -translate-y-1/2
                 hidden md:block
                 whitespace-nowrap rounded-lg border border-zinc-800 bg-black/95
                 px-2.5 py-1.5 text-[11px] font-semibold text-white
                 opacity-0 scale-90 transition-all duration-200
                 group-hover:opacity-100 group-hover:scale-100">
      Suivre sur Facebook
    </span>
    <a href="<?= $_fbHref ?>" target="_blank" rel="noopener noreferrer"
       class="flex h-11 w-11 items-center justify-center rounded-full
              bg-cyan-600 text-white shadow-lg shadow-cyan-900/40
              hover:bg-cyan-500 hover:scale-110 active:scale-95
              transition-all duration-200"
       aria-label="Page Facebook de KivuBoost">
      <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M9 8H7v3h2v9h4v-9h3.615L17 8h-4V6.157C13 5.374 13.5 5 14.143 5H17V0h-4c-3.26 0-5 1.83-5 4.857V8z"/>
      </svg>
    </a>
  </div>
  <?php endif; ?>

  <?php if ($_ig): ?>
  <div class="relative group">
    <span class="pointer-events-none absolute right-[3.5rem] top-1/2 -translate-y-1/2
                 hidden md:block
                 whitespace-nowrap rounded-lg border border-zinc-800 bg-black/95
                 px-2.5 py-1.5 text-[11px] font-semibold text-white
                 opacity-0 scale-90 transition-all duration-200
                 group-hover:opacity-100 group-hover:scale-100">
      Suivre sur Instagram
    </span>
    <a href="<?= $_igHref ?>" target="_blank" rel="noopener noreferrer"
       class="flex h-11 w-11 items-center justify-center rounded-full text-white shadow-lg
              bg-gradient-to-tr from-yellow-500 via-pink-500 to-purple-600
              hover:scale-110 active:scale-95 transition-all duration-200"
       aria-label="Instagram de KivuBoost">
      <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
      </svg>
    </a>
  </div>
  <?php endif; ?>

  <?php if (!$_wa && !$_fb && !$_ig): ?>
  <!-- Hub masqué tant que non configuré en admin -->
  <?php endif; ?>
</div>
