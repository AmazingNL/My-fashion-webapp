# AI Disclosure Statement

**Course:** Web Development 2 (Term 2.3)
**Project:** My Fashion Web App — a fashion boutique application with a custom PHP REST API backend and a Vue 3 / Vite frontend.

## 1. AI tools used

During development I used AI assistant tools (e.g.  / GitHub Copilot / Claude ) as a coding and learning aid.

## 2. How AI was used

AI assistance was used to support not replace my own work, mainly for:

- **Explaining concepts** I learned in the lectures (JWT authentication, REST conventions, the MVC pattern) so I could implement them correctly myself.
- **Scaffolding and boilerplate**, such as repetitive DTO/Mapper classes and route registrations, which I then reviewed, corrected, and adapted to my own architecture (Controller → Service → Repository).
- **Debugging**, by helping me interpret error messages and trace issues (e.g. routing, JWT validation, and CSS specificity problems).
- **Styling support**, including integrating the Tailwind CSS framework on top of my existing design system using CSS cascade layers, and building a responsive navigation bar with a mobile menu.
- **Reviewing and refining** code I had already written, for readability and consistency.

## 3. How AI was *not* used

- I did **not** generate the entire application from a single prompt. The architecture, data model, feature set, and use case are my own design.
- I did **not** submit AI output without reading and understanding it. Every AI-assisted change was reviewed, tested, and modified to fit the project.
- AI was not used to fabricate functionality I cannot explain.

## 4. Verification and understanding

All AI assisted code was manually verified by running the application (Docker setup, frontend dev server) and testing the endpoints (Postman collection, browser testing). I am able to explain how every part of the application works, including:

- the routing and dispatch flow (`app/Core/Router.php`),
- JWT authentication and role-based authorization (`app/Core/Middleware.php`),
- the layered backend (Controllers / Services / Repositories / DTOs / Mappers),
- the Vue frontend (routing, component structure, and state management stores),
- and the Tailwind CSS integration.

## 5. Academic integrity

The use of AI described above was as a supporting tool. The design decisions, problem solving, and final implementation are my own work, and I take full responsibility for the contents of this submission.

**Student:** *Amazinggrace Iruoma*
**Date:** *19/6/2026*
