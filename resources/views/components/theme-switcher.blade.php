
<div class="theme-switcher">
<div id="google_translate_element" style="margin-top: 9px; "></div>

<!-- <script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement(
    {
      pageLanguage: 'en', // Change 'en' to your website's default language
      includedLanguages: 'en,ur,fr,es,de,ar,hi,zh-CN', // Optional: limit to specific languages
      layout: google.translate.TranslateElement.InlineLayout.SIMPLE
    },
    'google_translate_element'
  );
}
</script>

<script type="text/javascript" 
  src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
</script> -->
    
    <button class="theme-btn" onclick="setTheme('dark-blue')" title="Dark Blue">
        <span style="background: linear-gradient(135deg, #1e3a8a 50%, #ffffff 50%);"></span>
    </button>
    <button class="theme-btn" onclick="setTheme('dark')" title="Dark Mode">
        <span style="background: linear-gradient(135deg, #000000 50%, #ffffff 50%);"></span>
    </button>
    <button class="theme-btn" onclick="setTheme('light')" title="Light Mode">
        <span style="background: linear-gradient(135deg, #ffffff 50%, #3b82f6 50%);"></span>
    </button>
    <button class="theme-btn" onclick="setTheme('olive-gold')" title="Olive Gold">
        <span style="background: linear-gradient(135deg, #556905 50%, #F1C705 50%);"></span>
    </button>
</div>

<style>
.theme-switcher {       
    position: fixed;
    top: 30px;
    right: 20px;
    z-index: 10000;
    display: flex;
    gap: 8px;
    background: var(--bg-card);
    backdrop-filter: blur(10px);
    padding: 12px;
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    border: 1px solid var(--border);
}

.theme-btn {
    width: 40px;
    height: 40px;
    border: 2px solid var(--border);
    border-radius: 50%;
    cursor: pointer;
    padding: 0;
    overflow: hidden;
    transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
    background: transparent;
}

.theme-btn:hover {
    transform: scale(1.15);
    border-color: var(--accent);
    box-shadow: 0 0 12px var(--accent);
}

.theme-btn span {
    display: block;
    width: 100%;
    height: 100%;
    border-radius: 50%;
}
</style>

<script>
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
}

// Load saved theme
const savedTheme = localStorage.getItem('theme') || 'dark-blue';
document.documentElement.setAttribute('data-theme', savedTheme);
</script>
