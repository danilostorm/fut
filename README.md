# ⚽ Fut Arena — Tema WordPress para Sites de Esportes

Tema WordPress profissional, dark-themed e totalmente otimizado para SEO e Core Web Vitals. Ideal para sites de notícias esportivas, blogs de futebol, vôlei, basquete e demais esportes.

---

## 🚀 Features

### 🎯 SEO Completo
- **Schema.org** (NewsArticle, BreadcrumbList, WebSite) — Google entender sua estrutura
- **JSON-LD** — dados estruturados em todas as páginas
- **Meta tags Open Graph** — compartilhamento perfeito no Facebook, WhatsApp, Telegram
- **Twitter Cards** — visual rico no Twitter/X
- **Canonical URLs** — evita conteúdo duplicado
- **Breadcrumbs** — navegação estruturada + Schema.org
- **Sitemap XML** — WordPress 5.5+ nativo
- **Meta descriptions** — automáticas por post

### ⚡ Performance / Core Web Vitals
- **CSS crítico inline** — First Contentful Paint rápido
- **Lazy loading nativo** — imagens carregam só quando entram na tela
- **Preload de assets** — fontes e CSS principal priorizados
- **LCP otimizado** — imagem do hero carrega primeiro (fetchpriority=high)
- **Remoção de bloat** — emojis, embeds desnecessários, query strings removidos
- **Cache headers** — Cache-Control otimizado
- **Heartbeat reduzido** — economia de recursos do servidor
- **Defer em JS** — JavaScript não bloqueia renderização

### 🔒 Segurança
- **Security Headers** — X-Content-Type-Options, X-Frame-Options, XSS-Protection, Referrer-Policy, Permissions-Policy

### 🎨 Design Dark Mode
- Tema escuro profissional com acentos neon (cyan, vermelho, amarelo)
- Responsivo mobile-first
- Reading progress bar
- Cards com hover effects
- Newsletter integrado
- Live score widget

### 📦 Custom Post Types & Taxonomies
- **Times** — archive de times com logo e estatísticas
- **Jogadores** — perfil de atletas
- **Campeonatos** — taxonomy hierárquica
- **Ligas** — taxonomy para organizar por liga

---

## 📁 Estrutura do Tema

```
fut-arena/
├── style.css              # CSS completo com variáveis CSS
├── functions.php          # Funções, SEO, Schema, lazy load
├── header.php             # Header com nav semântico
├── footer.php             # Footer com colunas
├── index.php              # Homepage com hero + grids
├── single.php             # Post individual completo
├── sidebar.php            # Sidebar com widgets
├── search.php             # Página de busca
├── 404.php                # Página de erro
├── screenshot.php          # Template de screenshot
├── screenshot.png          # Preview visual do tema
└── assets/
    ├── js/main.js         # JavaScript otimizado
    └── img/               # Imagens do tema
```

---

## 🛠️ Instalação

1. Clone ou copie a pasta `fut-arena` para `/wp-content/themes/` do seu WordPress
2. Ative o tema em **Aparência → Temas**
3. Configure o logo em **Aparência → Personalizar → Identidade do Site**
4. Crie menus em **Aparência → Menus**
5. Use os widgets em **Aparência → Widgets**

---

## 📝 Customização

### Cores (CSS Variables)
```css
:root {
  --fa-primary: #00d4ff;    /* Cyan neon */
  --fa-secondary: #ff4757;   /* Vermelho */
  --fa-accent: #ffd32a;     /* Amarelo */
  --fa-dark: #0a0e17;       /* Background */
  --fa-dark-2: #131b2e;     /* Cards */
  --fa-text: #e8eaf6;       /* Texto */
}
```

### Google Fonts
O tema usa **Inter** (padrão) e **JetBrains Mono** — modifique em `functions.php` se preferir outras fontes.

### Hooks & Filtros
```php
// Adicionar custom logo size
add_image_size('fut_hero', 1280, 720, true);
add_image_size('fut_card', 640, 360, true);

// Custom excerpt length
add_filter('excerpt_length', function() { return 25; });
```

---

## 📊 SEO Checklist

- ✅ Schema.org NewsArticle em posts
- ✅ Schema.org WebSite + SearchAction
- ✅ Schema.org BreadcrumbList
- ✅ Open Graph tags
- ✅ Twitter Cards
- ✅ Canonical URL
- ✅ Meta description
- ✅ Robots meta (index, follow)
- ✅ Article published/modified time
- ✅ Author attribution
- ✅ Article section (categoria)
- ✅ Image Open Graph
- ✅ hreflang (pt-BR)
- ✅ Sitemap XML nativo
- ✅ Internal linking (related posts)
- ✅ Mobile-first responsive
- ✅ Core Web Vitals ready

---

## 🌐 Requisitos

- WordPress 6.0+
- PHP 8.0+
- Servidor com HTTPS (recomendado)

---

## 📄 Licença

GNU General Public License v2 or later

---

Feito com ❤️ por [HostStorm](https://hoststorm.net)
