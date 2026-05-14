import Alpine from 'alpinejs';
import $ from 'jquery';
import AOS from 'aos';
import collapse from '@alpinejs/collapse';
import select2 from 'select2';
import axios from 'axios';
import { Chart, registerables } from 'chart.js';

// 0. Industry Standard Setup
Chart.register(...registerables);
window.axios = axios;
window.Chart = Chart;

// 1. Establish absolute global jQuery
window.jQuery = window.$ = $;

// 2. Import and Initialize Select2
// We try both the factory call and side-effect to ensure attachment on jQuery 4.0
if (typeof select2 === 'function') {
    select2($);
}

// 3. Setup Alpine
window.Alpine = Alpine;
window.AOS = AOS;
Alpine.plugin(collapse);
Alpine.start();

// 4. Global UI Logic
$(function() {
    // Standardize AOS
    AOS.init({ duration: 800, once: true });

    // 5. Global Page Loader & Transition System
    const createLoader = () => {
        if ($('#global-loader').length) return;
        $('body').append('<div id="global-loader" style="position:fixed;top:0;left:0;width:0;height:3px;background:var(--accent);z-index:9999;transition:width 0.3s ease;box-shadow:0 0 10px var(--accent);"></div>');
    };

    const startLoading = () => {
        createLoader();
        $('#global-loader').css('width', '10%').show();
        setTimeout(() => $('#global-loader').css('width', '60%'), 100);
    };

    const finishLoading = () => {
        $('#global-loader').css('width', '100%');
        setTimeout(() => $('#global-loader').fadeOut(200, function() { $(this).css('width', '0'); }), 300);
    };

    // Smooth exit on link clicks
    $(document).on('click', 'a', function(e) {
        const href = $(this).attr('href');
        const target = $(this).attr('target');
        
        // Only trigger for internal links without targets or modifiers
        if (href && href.startsWith('/') && !target && !e.ctrlKey && !e.shiftKey && !e.metaKey && !href.includes('#')) {
            startLoading();
            $('body').css('opacity', '0.6').css('transition', 'opacity 0.2s ease');
        }
    });

    // Smooth exit on form submissions
    $(document).on('submit', 'form', function() {
        startLoading();
        $('body').css('opacity', '0.6').css('transition', 'opacity 0.2s ease');
    });

    // Handle back/forward cache (safari/ios fix)
    window.onpageshow = function(event) {
        if (event.persisted) {
            finishLoading();
            $('body').css('opacity', '1');
        }
    };

    // Aggressive Global Select2 Initialization
    const initSelect2 = () => {
        if (typeof $.fn.select2 === 'function') {
            $('select').each(function() {
                const el = $(this);
                if (!el.hasClass('select2-hidden-accessible') && !el.hasClass('not-searchable')) {
                    el.select2({
                        width: '100%',
                        placeholder: el.attr('placeholder') || 'Select Option',
                        allowClear: true
                    });
                }
            });
        } else {
            console.error('Select2: jQuery plugin not found on $.fn. Ensure jQuery 4.0 compatibility.');
        }
    };

    // Run on boot and expose to window
    initSelect2();
    window.initGlobalSelect2 = initSelect2;
});

console.log('Mini Lara: Stack Initialized (jQuery 4.0 + Select2 4.1)');
