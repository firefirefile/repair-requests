<div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
     x-init="if (darkMode) document.documentElement.classList.add('dark');
             else document.documentElement.classList.remove('dark');
             $watch('darkMode', value => {
                 localStorage.setItem('darkMode', value);
                 if (value) {
                     document.documentElement.classList.add('dark');
                 } else {
                     document.documentElement.classList.remove('dark');
                 }
             })"
     class="dark-mode-toggle cursor-pointer"
     @click="darkMode = !darkMode"
     title="Toggle dark mode">
    <!-- Sun icon (shown in dark mode) -->
    <svg x-show="darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
    <!-- Moon icon (shown in light mode) -->
    <svg x-show="!darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>
</div>
