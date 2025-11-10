Create a React TypeScript project called "SocialLead Pro" with the following structure:

Tech Stack:
- Next.js 14 with App Router
- TypeScript
- Tailwind CSS
- Shadcn/ui components
- Lucide React for icons

Project Structure:
src/
  app/
    layout.tsx
    page.tsx
    globals.css
  components/
    ui/ (button, card, input, etc.)
    layout/ (sidebar, header, main layout)
  lib/ (utils, constants)
  types/ (TypeScript interfaces)

Requirements:
1. Create a main layout with:
   - Sidebar navigation (collapsible)
   - Top header with user menu and theme toggle
   - Main content area

2. Sidebar should have these menu items:
   - Dashboard
   - Conversations
   - Calendars
   - Contacts
   - Opportunities
   - AI Agents
   - Marketing
   - Media Storage
   - Reputation
   - Reporting
   - App Marketplace
   - Learning Center
   - Resource Center
   - Settings

3. Implement dark/light theme system
4. Make it fully responsive
5. Use Shadcn/ui components for consistent design

Provide the complete code for:
- package.json with all dependencies
- layout.tsx (root layout)
- page.tsx (dashboard page)
- globals.css (Tailwind config)
- Required component files