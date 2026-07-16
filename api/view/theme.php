<!-- Apply saved theme before first paint to avoid a flash -->
    <script>
        (function () {
            const saved = localStorage.getItem('venos-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = saved ? saved === 'dark' : prefersDark;
            document.documentElement.classList.toggle('dark', isDark);
        })();
    </script>

    <!-- Tailwind Play CDN: zero build step needed -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        surface: 'rgb(var(--color-surface) / <alpha-value>)',
                        panel: 'rgb(var(--color-panel) / <alpha-value>)',
                        card: 'rgb(var(--color-card) / <alpha-value>)',
                        cardhover: 'rgb(var(--color-cardhover) / <alpha-value>)',
                        accent: '#f5a623',
                        white: 'rgb(var(--color-overlay) / <alpha-value>)',
                        gray: {
                            100: 'rgb(var(--color-gray-100) / <alpha-value>)',
                            200: 'rgb(var(--color-gray-200) / <alpha-value>)',
                            300: 'rgb(var(--color-gray-300) / <alpha-value>)',
                            400: 'rgb(var(--color-gray-400) / <alpha-value>)',
                            500: 'rgb(var(--color-gray-500) / <alpha-value>)',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>
    <style>
        /* Light theme (default) */
        :root {
            --color-surface: 245 246 248;
            --color-panel: 255 255 255;
            --color-card: 255 255 255;
            --color-cardhover: 241 242 245;
            --color-overlay: 0 0 0;      /* used for the white/N "hairline" utilities */
            --color-gray-100: 17 24 39;
            --color-gray-200: 31 41 55;
            --color-gray-300: 55 65 81;
            --color-gray-400: 107 114 128;
            --color-gray-500: 156 163 175;
        }
        /* Dark theme */
        .dark {
            --color-surface: 13 14 18;
            --color-panel: 21 23 29;
            --color-card: 27 30 37;
            --color-cardhover: 32 35 43;
            --color-overlay: 255 255 255;
            --color-gray-100: 243 244 246;
            --color-gray-200: 229 231 235;
            --color-gray-300: 209 213 219;
            --color-gray-400: 156 163 175;
            --color-gray-500: 107 114 128;
        }
    </style>

    <!-- Theme toggle button behavior (shared across every page) -->
    <script>
        function applyThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark');
            document.getElementById('theme-icon-sun').classList.toggle('hidden', !isDark);
            document.getElementById('theme-icon-moon').classList.toggle('hidden', isDark);
        }

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('venos-theme', isDark ? 'dark' : 'light');
            applyThemeIcon();
        }

        document.addEventListener('DOMContentLoaded', () => {
            applyThemeIcon();
            document.getElementById('theme-toggle-btn').addEventListener('click', toggleTheme);
        });
    </script>