<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ChocApp — Presentación Oficial</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/lucide-static@0.378.0/font/lucide.min.css" rel="stylesheet">
  <style>
    /* ══════════════════════════════════════════════
   DESIGN SYSTEM TOKENS — ChocApp Official
══════════════════════════════════════════════ */
    :root {
      /* Surface */
      --surface: #f8f9fa;
      --surface-dim: #d9dadb;
      --surface-container-lowest: #ffffff;
      --surface-container-low: #f3f4f5;
      --surface-container: #edeeef;
      --surface-container-high: #e7e8e9;
      --surface-container-highest: #e1e3e4;
      --on-surface: #191c1d;
      --on-surface-variant: #5c403c;
      --inverse-surface: #2e3132;
      --inverse-on-surface: #f0f1f2;
      --outline: #916f6b;
      --outline-variant: #e6bdb8;

      /* Primary — Emergency Red */
      --primary: #bb020f;
      --primary-container: #e02a25;
      --on-primary: #ffffff;
      --on-primary-container: #fffbff;
      --inverse-primary: #ffb4aa;
      --surface-tint: #bf0811;
      --primary-fixed: #ffdad5;
      --primary-fixed-dim: #ffb4aa;
      --on-primary-fixed: #410001;
      --on-primary-fixed-variant: #930008;

      /* Secondary — Dark Navy */
      --secondary: #5d5c74;
      --on-secondary: #ffffff;
      --secondary-container: #e2e0fc;
      --on-secondary-container: #63627a;
      --secondary-fixed: #e2e0fc;
      --secondary-fixed-dim: #c6c4df;
      --on-secondary-fixed: #1a1a2e;
      --on-secondary-fixed-variant: #45455b;

      /* Tertiary — Alert Amber */
      --tertiary: #805200;
      --on-tertiary: #ffffff;
      --tertiary-container: #a16900;
      --on-tertiary-container: #fffbff;
      --tertiary-fixed: #ffddb4;
      --tertiary-fixed-dim: #ffb955;
      --on-tertiary-fixed: #291800;
      --on-tertiary-fixed-variant: #633f00;

      /* Error */
      --error: #ba1a1a;
      --on-error: #ffffff;
      --error-container: #ffdad6;
      --on-error-container: #93000a;

      /* Background */
      --background: #f8f9fa;
      --on-background: #191c1d;

      /* Semantic extras (from component spec) */
      --action-btn: #E8302A;
      --navy: #1A1A2E;
      --success: #27AE60;
      --amber: #F5A623;

      /* Typography scale */
      --font: 'Inter', sans-serif;
      --display-lg: 700 32px/40px var(--font);
      --headline-md: 700 24px/32px var(--font);
      --headline-sm: 600 20px/28px var(--font);
      --body-lg: 400 18px/28px var(--font);
      --body-md: 400 16px/24px var(--font);
      --body-sm: 400 14px/20px var(--font);
      --label-lg: 600 14px/20px var(--font);
      --label-md: 600 12px/16px var(--font);

      /* Radius */
      --r-sm: 0.25rem;
      --r: 0.5rem;
      --r-md: 0.75rem;
      --r-lg: 1rem;
      --r-xl: 1.5rem;
      --r-full: 9999px;

      /* Elevation */
      --shadow-card: 0 4px 12px rgba(0, 0, 0, 0.08);
      --shadow-elevated: 0 8px 24px rgba(0, 0, 0, 0.12);

      /* Spacing */
      --gutter: 16px;
      --margin: 20px;
      --tap: 56px;
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font: var(--body-md);
      background: var(--background);
      color: var(--on-background);
      -webkit-font-smoothing: antialiased;
    }

    /* ══════════════════════════════════════════════
   LAYOUT
══════════════════════════════════════════════ */
    .section {
      padding: 80px var(--margin);
    }

    .container {
      max-width: 1160px;
      margin: 0 auto;
      width: 100%;
    }

    /* ══════════════════════════════════════════════
   COMPONENTS
══════════════════════════════════════════════ */

    /* Badge */
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 12px;
      border-radius: var(--r-full);
      font: var(--label-md);
      letter-spacing: 0.02em;
    }

    .badge-primary {
      background: rgba(232, 48, 42, 0.12);
      color: var(--primary);
    }

    .badge-navy {
      background: rgba(26, 26, 46, 0.08);
      color: var(--navy);
    }

    .badge-success {
      background: rgba(39, 174, 96, 0.12);
      color: var(--success);
    }

    .badge-amber {
      background: rgba(245, 166, 35, 0.12);
      color: var(--tertiary);
    }

    /* Buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      height: var(--tap);
      padding: 0 24px;
      border: none;
      border-radius: var(--r);
      font: var(--label-lg);
      cursor: pointer;
      transition: opacity 0.15s, transform 0.15s, box-shadow 0.15s;
      text-decoration: none;
      white-space: nowrap;
    }

    .btn:hover {
      opacity: 0.88;
      transform: translateY(-1px);
    }

    .btn-primary {
      background: var(--action-btn);
      color: var(--on-primary);
      box-shadow: 0 4px 16px rgba(232, 48, 42, 0.30);
    }

    .btn-secondary {
      background: var(--navy);
      color: #fff;
    }

    .btn-outline {
      background: transparent;
      color: var(--navy);
      border: 1.5px solid var(--outline-variant);
    }

    /* Card */
    .card {
      background: var(--surface-container-lowest);
      border-radius: var(--r-md);
      box-shadow: var(--shadow-card);
      padding: 24px;
    }

    .card-high {
      box-shadow: var(--shadow-elevated);
    }

    /* Input mock */
    .input-mock {
      height: var(--tap);
      border: 1px solid #D1D5DB;
      border-radius: var(--r-md);
      padding: 0 16px;
      display: flex;
      align-items: center;
      font: var(--body-md);
      color: var(--on-surface-variant);
      background: var(--surface-container-lowest);
    }

    .input-mock.focused {
      border: 2px solid var(--navy);
      color: var(--on-surface);
    }

    /* Section label */
    .section-eyebrow {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 12px;
    }

    .section-eyebrow span {
      font: var(--label-md);
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--primary);
    }

    .eyebrow-line {
      width: 32px;
      height: 2px;
      background: var(--primary);
      border-radius: 2px;
    }

    /* Divider */
    .divider {
      height: 1px;
      background: var(--surface-container-high);
      margin: 0;
    }

    /* Step dot */
    .step-dot {
      width: 40px;
      height: 40px;
      border-radius: var(--r-full);
      background: var(--primary-fixed);
      color: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font: var(--label-lg);
      flex-shrink: 0;
    }

    .step-dot.active {
      background: var(--primary);
      color: var(--on-primary);
    }

    /* Icon container */
    .icon-wrap {
      width: 48px;
      height: 48px;
      border-radius: var(--r-md);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .icon-wrap-primary {
      background: var(--primary-fixed);
      color: var(--primary);
    }

    .icon-wrap-navy {
      background: var(--secondary-fixed);
      color: var(--navy);
    }

    .icon-wrap-amber {
      background: var(--tertiary-fixed);
      color: var(--tertiary);
    }

    .icon-wrap-success {
      background: rgba(39, 174, 96, 0.12);
      color: var(--success);
    }

    /* ══════════════════════════════════════════════
   NAV
══════════════════════════════════════════════ */
    .nav {
      position: sticky;
      top: 0;
      z-index: 100;
      background: rgba(248, 249, 250, 0.92);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--surface-container-high);
    }

    .nav-inner {
      max-width: 1160px;
      margin: 0 auto;
      padding: 0 var(--margin);
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .nav-logo {
      font: var(--headline-sm);
      color: var(--on-surface);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .nav-logo-dot {
      width: 10px;
      height: 10px;
      border-radius: var(--r-full);
      background: var(--primary);
    }

    .nav-links {
      display: flex;
      gap: 32px;
      list-style: none;
    }

    .nav-links a {
      font: var(--label-lg);
      color: var(--on-surface-variant);
      text-decoration: none;
      transition: color 0.15s;
    }

    .nav-links a:hover {
      color: var(--on-surface);
    }

    .nav-cta {
      display: flex;
      align-items: center;
    }

    /* ══════════════════════════════════════════════
   HERO
══════════════════════════════════════════════ */
    .hero {
      background: var(--surface-container-lowest);
      border-bottom: 1px solid var(--surface-container-high);
      padding: 60px var(--margin) 64px;
      position: relative;
      overflow: hidden;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: -120px;
      right: -80px;
      width: 520px;
      height: 520px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(187, 2, 15, 0.06) 0%, transparent 70%);
      pointer-events: none;
    }

    .hero-inner {
      max-width: 1160px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr 400px;
      gap: 64px;
      align-items: center;
    }

    .hero-kicker {
      margin-bottom: 20px;
    }

    .hero-title {
      font: var(--display-lg);
      letter-spacing: -0.02em;
      color: var(--on-surface);
      margin-bottom: 16px;
      font-size: clamp(28px, 4vw, 48px);
      line-height: 1.1;
    }

    .hero-title em {
      font-style: normal;
      color: var(--primary);
    }

    .hero-body {
      font: var(--body-lg);
      color: var(--on-surface-variant);
      margin-bottom: 32px;
      max-width: 520px;
    }

    .hero-actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 48px;
    }

    .hero-stats {
      display: flex;
      gap: 32px;
      padding-top: 32px;
      border-top: 1px solid var(--surface-container-high);
    }

    .hero-stat-num {
      font: var(--display-lg);
      letter-spacing: -0.02em;
      color: var(--primary);
      display: block;
    }

    .hero-stat-label {
      font: var(--body-sm);
      color: var(--on-surface-variant);
      max-width: 120px;
    }

    /* Phone mockup in hero */
    .phone-wrap {
      position: relative;
      display: flex;
      justify-content: center;
    }

    .phone-wrap::before {
      content: '';
      position: absolute;
      inset: -32px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(187, 2, 15, 0.08) 0%, transparent 70%);
      pointer-events: none;
    }

    .phone {
      width: 500px;
      border-radius: 36px;
      padding: 20px 14px;
      box-shadow: var(--shadow-elevated), 0 0 0 1px rgba(255, 255, 255, 0.06);
      position: relative;
      z-index: 1;
    }

    .phone-notch {
      width: 60px;
      height: 5px;
      background: rgba(255, 255, 255, 0.12);
      border-radius: var(--r-full);
      margin: 0 auto 20px;
    }

    .phone-screen {
      background: #0f0f0f;
      border-radius: 20px;
      padding: 20px 14px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 16px;
      min-height: 340px;
    }

    .phone-app-title {
      font: var(--label-lg);
      color: rgba(255, 255, 255, 0.7);
      letter-spacing: 0.06em;
      text-transform: uppercase;
      font-size: 10px;
    }

    .sos-btn {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: radial-gradient(circle at 35% 35%, #E8302A, #8B0000);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 2px;
      box-shadow: 0 0 0 8px rgba(232, 48, 42, 0.08), 0 0 0 20px rgba(232, 48, 42, 0.04), 0 8px 32px rgba(232, 48, 42, 0.45);
      animation: pulse-sos 2.4s ease-in-out infinite;
      cursor: pointer;
    }

    .sos-label {
      font-weight: 700;
      font-size: 18px;
      color: white;
      font-family: var(--font);
      letter-spacing: 0.04em;
    }

    .sos-sub {
      font-size: 8px;
      color: rgba(255, 255, 255, 0.75);
      font-family: var(--font);
      letter-spacing: 0.12em;
      text-transform: uppercase;
    }

    .phone-steps-preview {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .phone-step-row {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 8px;
      padding: 8px 10px;
    }

    .phone-step-indicator {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .phone-step-bar {
      height: 5px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: var(--r-full);
      flex: 1;
    }

    .phone-step-bar.done {
      background: rgba(39, 174, 96, 0.4);
    }

    .phone-step-bar.active {
      background: rgba(232, 48, 42, 0.4);
    }

    @keyframes pulse-sos {

      0%,
      100% {
        transform: scale(1);
        box-shadow: 0 0 0 8px rgba(232, 48, 42, 0.08), 0 0 0 20px rgba(232, 48, 42, 0.04), 0 8px 32px rgba(232, 48, 42, 0.45);
      }

      50% {
        transform: scale(1.04);
        box-shadow: 0 0 0 12px rgba(232, 48, 42, 0.10), 0 0 0 28px rgba(232, 48, 42, 0.05), 0 12px 40px rgba(232, 48, 42, 0.50);
      }
    }

    /* ══════════════════════════════════════════════
   PROBLEMA
══════════════════════════════════════════════ */
    .problema {
      background: var(--surface);
    }

    .problema-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
      margin-top: 40px;
    }

    .problema-card {
      padding: 28px 24px;
      border-left: 3px solid transparent;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .problema-card:hover {
      border-left-color: var(--primary);
      box-shadow: var(--shadow-elevated);
    }

    .problema-card-title {
      font: var(--headline-sm);
      color: var(--on-surface);
      margin: 16px 0 8px;
    }

    .problema-card-body {
      font: var(--body-md);
      color: var(--on-surface-variant);
      line-height: 24px;
    }

    /* ══════════════════════════════════════════════
   SOLUCIÓN / PASOS
══════════════════════════════════════════════ */
    .solucion {
      background: var(--surface-container-lowest);
      border-top: 1px solid var(--surface-container-high);
      border-bottom: 1px solid var(--surface-container-high);
    }

    .solucion-inner {
      display: grid;
      grid-template-columns: 1fr 1.1fr;
      gap: 64px;
      align-items: start;
    }

    .pasos-list {
      display: flex;
      flex-direction: column;
      margin-top: 36px;
    }

    .paso-item {
      display: flex;
      gap: 16px;
      padding: 20px 0;
      border-bottom: 1px solid var(--surface-container-high);
      transition: background 0.15s;
      cursor: default;
    }

    .paso-item:last-child {
      border-bottom: none;
    }

    .paso-item:hover .step-dot {
      background: var(--primary);
      color: var(--on-primary);
    }

    .paso-content {
      flex: 1;
    }

    .paso-title {
      font: var(--label-lg);
      color: var(--on-surface);
      margin-bottom: 4px;
    }

    .paso-desc {
      font: var(--body-sm);
      color: var(--on-surface-variant);
      line-height: 20px;
    }

    /* Paso visual panel */
    .paso-panel {
      margin-top: 36px;
      position: sticky;
      top: 80px;
    }

    .paso-panel-screen {
      background: var(--surface-container-lowest);
      border: 1px solid var(--surface-container-high);
      border-radius: var(--r-lg);
      box-shadow: var(--shadow-elevated);
      overflow: hidden;
    }

    .panel-header {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 16px 20px;
      border-bottom: 1px solid var(--surface-container-high);
      background: var(--surface-container-low);
    }

    .panel-dots span {
      display: inline-block;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      margin-right: 4px;
    }

    .panel-title {
      font: var(--label-lg);
      color: var(--on-surface);
      margin-left: 4px;
    }

    .panel-body {
      padding: 24px 20px;
    }

    .panel-step-indicator {
      display: flex;
      gap: 6px;
      margin-bottom: 20px;
    }

    .panel-step-pip {
      height: 4px;
      border-radius: var(--r-full);
      flex: 1;
      background: var(--surface-container-high);
    }

    .panel-step-pip.done {
      background: var(--success);
    }

    .panel-step-pip.active {
      background: var(--primary);
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-label {
      font: var(--label-md);
      color: var(--on-surface-variant);
      letter-spacing: 0.02em;
      display: block;
      margin-bottom: 6px;
      text-transform: uppercase;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .photo-area {
      border: 1.5px dashed var(--outline-variant);
      border-radius: var(--r-md);
      padding: 20px;
      text-align: center;
      background: var(--primary-fixed);
      margin-top: 4px;
    }

    .photo-area-icon {
      font-size: 20px;
      margin-bottom: 6px;
    }

    .photo-area-text {
      font: var(--body-sm);
      color: var(--on-surface-variant);
    }

    /* ══════════════════════════════════════════════
   BENEFICIOS
══════════════════════════════════════════════ */
    .beneficios {
      background: var(--surface);
    }

    .beneficios-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-top: 40px;
    }

    .beneficio-card {
      padding: 28px 24px;
      transition: box-shadow 0.2s, transform 0.2s;
    }

    .beneficio-card:hover {
      box-shadow: var(--shadow-elevated);
      transform: translateY(-3px);
    }

    .beneficio-title {
      font: var(--headline-sm);
      color: var(--on-surface);
      margin: 16px 0 8px;
    }

    .beneficio-body {
      font: var(--body-sm);
      color: var(--on-surface-variant);
      line-height: 20px;
    }

    /* ══════════════════════════════════════════════
   PÚBLICO OBJETIVO
══════════════════════════════════════════════ */
    .publico {
      background: var(--navy);
      position: relative;
      overflow: hidden;
    }

    .publico::before {
      content: '';
      position: absolute;
      top: -100px;
      right: -100px;
      width: 400px;
      height: 400px;
      border-radius: 50%;
      background: rgba(187, 2, 15, 0.08);
      pointer-events: none;
    }

    .publico .section-eyebrow span {
      color: var(--inverse-primary);
    }

    .publico .eyebrow-line {
      background: var(--inverse-primary);
    }

    .publico-title {
      font: var(--display-lg);
      letter-spacing: -0.02em;
      color: var(--inverse-on-surface);
      margin-bottom: 12px;
    }

    .publico-subtitle {
      font: var(--body-lg);
      color: rgba(255, 255, 255, 0.55);
      margin-bottom: 40px;
    }

    .publico-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
    }

    .publico-card {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: var(--r-md);
      padding: 24px;
      transition: background 0.2s, border-color 0.2s;
    }

    .publico-card:hover {
      background: rgba(255, 255, 255, 0.09);
      border-color: rgba(187, 2, 15, 0.35);
    }

    .publico-card-title {
      font: var(--label-lg);
      color: #fff;
      margin: 12px 0 6px;
    }

    .publico-card-body {
      font: var(--body-sm);
      color: rgba(255, 255, 255, 0.55);
      line-height: 20px;
    }

    /* ══════════════════════════════════════════════
   ARQUITECTURA / REPORTE
══════════════════════════════════════════════ */
    .reporte {
      background: var(--surface-container-lowest);
      border-top: 1px solid var(--surface-container-high);
    }

    .reporte-inner {
      display: grid;
      grid-template-columns: 1fr 1.1fr;
      gap: 64px;
      align-items: start;
    }

    .feature-list {
      display: flex;
      flex-direction: column;
      gap: 20px;
      margin-top: 32px;
    }

    .feature-row {
      display: flex;
      gap: 16px;
      align-items: flex-start;
    }

    .feature-check {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: rgba(39, 174, 96, 0.12);
      color: var(--success);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      flex-shrink: 0;
      margin-top: 2px;
    }

    .feature-text {
      font: var(--body-md);
      color: var(--on-surface-variant);
      line-height: 24px;
    }

    .feature-text strong {
      color: var(--on-surface);
      font-weight: 600;
    }

    /* PDF card mockup */
    .pdf-mockup {
      background: var(--surface-container-lowest);
      border: 1px solid var(--surface-container-high);
      border-radius: var(--r-lg);
      box-shadow: var(--shadow-elevated);
      overflow: hidden;
      margin-top: 40px;
    }

    .pdf-header {
      padding: 16px 20px;
      background: var(--primary);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .pdf-header-title {
      font: var(--label-lg);
      color: var(--on-primary);
    }

    .pdf-badge {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      font: var(--label-md);
      padding: 3px 10px;
      border-radius: var(--r-full);
    }

    .pdf-body {
      padding: 20px;
    }

    .pdf-section-title {
      font: var(--label-md);
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--outline);
      margin-bottom: 12px;
      margin-top: 20px;
    }

    .pdf-section-title:first-child {
      margin-top: 0;
    }

    .pdf-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 8px;
    }

    .pdf-field-label {
      font: var(--body-sm);
      color: var(--on-surface-variant);
      margin-bottom: 2px;
    }

    .pdf-field-value {
      font: var(--label-lg);
      color: var(--on-surface);
    }

    .pdf-divider {
      height: 1px;
      background: var(--surface-container-high);
      margin: 16px 0;
    }

    .pdf-photos {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 8px;
    }

    .pdf-photo {
      border-radius: var(--r-sm);
      aspect-ratio: 1;
      background: var(--surface-container);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--outline);
      font-size: 18px;
    }

    .pdf-footer {
      padding: 14px 20px;
      background: var(--surface-container-low);
      border-top: 1px solid var(--surface-container-high);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .pdf-footer-text {
      font: var(--body-sm);
      color: var(--on-surface-variant);
    }

    .pdf-export-btn {
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: var(--r);
      padding: 0 16px;
      height: 36px;
      font: var(--label-md);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    /* ══════════════════════════════════════════════
   CTA
══════════════════════════════════════════════ */
    .cta-section {
      background: var(--surface);
      border-top: 1px solid var(--surface-container-high);
      padding: 96px var(--margin);
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .cta-section::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 500px;
      height: 300px;
      border-radius: 50%;
      background: radial-gradient(ellipse, rgba(187, 2, 15, 0.06) 0%, transparent 70%);
      pointer-events: none;
    }

    .cta-title {
      font: var(--display-lg);
      letter-spacing: -0.02em;
      color: var(--on-surface);
      margin-bottom: 12px;
      font-size: clamp(24px, 3.5vw, 36px);
    }

    .cta-body {
      font: var(--body-lg);
      color: var(--on-surface-variant);
      max-width: 440px;
      margin: 0 auto 32px;
    }

    .cta-actions {
      display: flex;
      gap: 12px;
      justify-content: center;
      flex-wrap: wrap;
    }

    /* ══════════════════════════════════════════════
   FOOTER
══════════════════════════════════════════════ */
    footer {
      background: var(--inverse-surface);
      padding: 24px var(--margin);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .footer-logo {
      font: var(--label-lg);
      color: var(--inverse-on-surface);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .footer-logo-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--primary);
    }

    .footer-text {
      font: var(--body-sm);
      color: rgba(255, 255, 255, 0.35);
    }

    /* ══════════════════════════════════════════════
   FADE-IN ON SCROLL
══════════════════════════════════════════════ */
    .fade-in {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.5s ease, transform 0.5s ease;
    }

    .fade-in.visible {
      opacity: 1;
      transform: none;
    }

    /* Stagger */
    .stagger>* {
      transition-delay: calc(var(--i, 0) * 80ms);
    }

    /* ══════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════ */
    @media (max-width: 960px) {

      .hero-inner,
      .solucion-inner,
      .reporte-inner {
        grid-template-columns: 1fr;
        gap: 40px;
      }

      .phone-wrap {
        display: none;
      }

      .problema-grid,
      .publico-grid {
        grid-template-columns: 1fr;
      }

      .beneficios-grid {
        grid-template-columns: 1fr 1fr;
      }

      .nav-links {
        display: none;
      }

      .hero-stats {
        flex-wrap: wrap;
        gap: 20px;
      }
    }

    @media (max-width: 600px) {
      .beneficios-grid {
        grid-template-columns: 1fr;
      }

      .section {
        padding: 56px 16px;
      }

      .hero {
        padding: 56px 16px 48px;
      }

      footer {
        flex-direction: column;
        gap: 12px;
        text-align: center;
      }
    }
  </style>
</head>

<body>

  <!-- ╔══════════════════════════════════════════╗ -->
  <!-- ║  NAV                                     ║ -->
  <!-- ╚══════════════════════════════════════════╝ -->
  <nav class="nav">
    <div class="nav-inner">
      <div class="nav-logo">
        <div class="nav-logo-dot"></div>
        ChocApp
      </div>
      <ul class="nav-links">
        <li><a href="#problema">Problemática</a></li>
        <li><a href="#solucion">Solución</a></li>
        <li><a href="#beneficios">Beneficios</a></li>
        <li><a href="#reporte">Reporte</a></li>
      </ul>
      <div class="nav-cta">
        <a href="#cta" class="btn btn-primary" style="height:40px;padding:0 18px;font-size:13px;">Descargar</a>
      </div>
    </div>
  </nav>

  <!-- ╔══════════════════════════════════════════╗ -->
  <!-- ║  HERO                                    ║ -->
  <!-- ╚══════════════════════════════════════════╝ -->
  <section class="hero">
    <div class="hero-inner">
      <div>
        <div class="hero-kicker">
          <span class="badge badge-primary">Aplicación móvil · Android</span>
        </div>
        <h1 class="hero-title fade-in">
          Cuando el pánico toma el control,<br>
          <em>ChocApp actúa.</em>
        </h1>
        <p class="hero-body fade-in">
          Una guía paso a paso para los primeros minutos después de un accidente. Recopila evidencia, documenta el
          incidente y genera el reporte para tu aseguradora, todo desde tu celular.
        </p>
        <div class="hero-actions fade-in">
          <a href="#solucion" class="btn btn-primary">Ver cómo funciona</a>
          <a href="#beneficios" class="btn btn-outline">Beneficios</a>
        </div>
        <div class="hero-stats fade-in">
          <div>
            <span class="hero-stat-num">680K+</span>
            <span class="hero-stat-label">Accidentes viales al año en Colombia</span>
          </div>
          <div>
            <span class="hero-stat-num">72%</span>
            <span class="hero-stat-label">De afectados no saben qué hacer</span>
          </div>
          <div>
            <span class="hero-stat-num">3×</span>
            <span class="hero-stat-label">Más rápido con guía estructurada</span>
          </div>
        </div>
      </div>

      <!-- Phone mockup -->
      <div class="phone-wrap fade-in">
        <div class="phone">
          <!DOCTYPE html>

          <html class="light" lang="es">

          <head>
            <meta charset="utf-8" />
            <meta content="width=device-width, initial-scale=1.0" name="viewport" />
            <title>ChocApp - Dashboard</title>
            <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&amp;display=swap"
              rel="stylesheet" />
            <link
              href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
              rel="stylesheet" />
            <link
              href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
              rel="stylesheet" />
            <script id="tailwind-config">
              tailwind.config = {
                darkMode: "class",
                theme: {
                  extend: {
                    "colors": {
                      "surface-container-lowest": "#ffffff",
                      "on-primary-container": "#fffbff",
                      "on-primary": "#ffffff",
                      "surface-bright": "#f8f9fa",
                      "on-tertiary-container": "#fffbff",
                      "secondary-fixed": "#e2e0fc",
                      "on-tertiary-fixed-variant": "#633f00",
                      "surface-container": "#edeeef",
                      "surface-tint": "#bf0811",
                      "on-secondary-fixed-variant": "#45455b",
                      "on-error": "#ffffff",
                      "on-tertiary": "#ffffff",
                      "on-background": "#191c1d",
                      "outline": "#916f6b",
                      "primary": "#bb020f",
                      "surface-container-low": "#f3f4f5",
                      "outline-variant": "#e6bdb8",
                      "secondary": "#5d5c74",
                      "inverse-on-surface": "#f0f1f2",
                      "on-secondary-fixed": "#1a1a2e",
                      "inverse-primary": "#ffb4aa",
                      "on-surface": "#191c1d",
                      "tertiary": "#805200",
                      "on-secondary": "#ffffff",
                      "primary-container": "#e02a25",
                      "error-container": "#ffdad6",
                      "secondary-fixed-dim": "#c6c4df",
                      "tertiary-fixed": "#ffddb4",
                      "background": "#f8f9fa",
                      "inverse-surface": "#2e3132",
                      "surface-variant": "#e1e3e4",
                      "on-surface-variant": "#5c403c",
                      "surface-container-highest": "#e1e3e4",
                      "surface-container-high": "#e7e8e9",
                      "on-primary-fixed": "#410001",
                      "on-tertiary-fixed": "#291800",
                      "secondary-container": "#e2e0fc",
                      "error": "#ba1a1a",
                      "on-secondary-container": "#63627a",
                      "tertiary-fixed-dim": "#ffb955",
                      "surface-dim": "#d9dadb",
                      "surface": "#f8f9fa",
                      "tertiary-container": "#a16900",
                      "on-primary-fixed-variant": "#930008",
                      "primary-fixed": "#ffdad5",
                      "primary-fixed-dim": "#ffb4aa",
                      "on-error-container": "#93000a"
                    },
                    "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "full": "9999px"
                    },
                    "spacing": {
                      "margin-mobile": "20px",
                      "container-padding": "16px",
                      "unit": "4px",
                      "tap-target-min": "56px",
                      "gutter": "16px"
                    },
                    "fontFamily": {
                      "headline-sm": ["Inter"],
                      "display-lg": ["Inter"],
                      "body-sm": ["Inter"],
                      "headline-md": ["Inter"],
                      "label-md": ["Inter"],
                      "body-lg": ["Inter"],
                      "body-md": ["Inter"],
                      "label-lg": ["Inter"]
                    },
                    "fontSize": {
                      "headline-sm": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                      "display-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                      "body-sm": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                      "headline-md": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
                      "label-md": ["12px", { "lineHeight": "16px", "letterSpacing": "0.02em", "fontWeight": "600" }],
                      "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                      "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                      "label-lg": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "600" }]
                    }
                  },
                },
              }
            </script>
            <style>
              body {
                font-family: 'Inter', sans-serif;
                -webkit-tap-highlight-color: transparent;
              }

              .hide-scrollbar::-webkit-scrollbar {
                display: none;
              }

              .hide-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
              }

              .emergency-glow {
                box-shadow: 0 0 20px rgba(232, 48, 42, 0.4);
                animation: pulse-red 2s infinite;
              }

              @keyframes pulse-red {
                0% {
                  transform: scale(1);
                  box-shadow: 0 0 0 0 rgba(232, 48, 42, 0.7);
                }

                70% {
                  transform: scale(1.05);
                  box-shadow: 0 0 0 15px rgba(232, 48, 42, 0);
                }

                100% {
                  transform: scale(1);
                  box-shadow: 0 0 0 0 rgba(232, 48, 42, 0);
                }
              }
            </style>
            <style>
              body {
                min-height: max(884px, 100dvh);
              }
            </style>
          </head>

          <body class="bg-surface text-on-surface min-h-screen">
            <!-- TopAppBar -->
            <header
              class="bg-surface dark:bg-surface flex justify-between items-center px-margin-mobile h-tap-target-min w-full z-50 fixed top-0 left-0">
              <div class="flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-full overflow-hidden bg-surface-container-high border border-outline-variant">
                  <img alt="User Profile Picture" class="w-full h-full object-cover"
                    data-alt="A professional close-up headshot of a friendly man with a modern hairstyle in a bright, corporate setting. The lighting is soft and high-key, reflecting a light-mode UI aesthetic. The composition is clean and minimalist, focusing on reliability and professionalism within a high-end automotive app environment. The background is a blurred, pristine office space with accents of primary red."
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuDVRX-gK7qKHIEyqRVBGysWENmzfuFYKx-KT4Lzpjrs6aGcbjyhHTOKmJKTxGOYOT2yDvznbdh0xPEqfRlDHKecgs6dLN0d5xad29sfSZAArAkaPee2u_DVrQ-MDbZoClw6SmLtmodevpQPd5OTluK4RgqyvoqFFPoL7LBfr0-KBuTVYWBxfp4Ws7XMKlL6Wl0b_crxHd4EFD-wxxbH12QoZagEKRRHxccVUECwSXi5Akx31Ek-e-IShrwbZUXiVxCC-e7pxjMfGIU" />
                </div>
                <div>
                  <span class="font-label-md text-label-md text-on-surface-variant block">Hola,</span>
                  <span
                    class="font-headline-sm text-headline-sm text-primary dark:text-primary-fixed block leading-tight">Alejandro</span>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button
                  class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-surface-container-low transition-colors Active: opacity-80 transition-opacity">
                  <span class="material-symbols-outlined text-primary" data-icon="notifications">notifications</span>
                </button>
              </div>
            </header>
            <main class="pt-20 px-margin-mobile space-y-8">
              <!-- Hero Section: Panic Button -->
              <section class="flex flex-col items-center justify-center py-6">
                <button
                  class="emergency-glow relative w-[120px] h-[120px] bg-primary rounded-full flex items-center justify-center transition-all active:scale-90 z-10"
                  id="panicButton">
                  <span class="material-symbols-outlined text-white text-[56px]" data-icon="emergency_home"
                    data-weight="fill" style="font-variation-settings: 'FILL' 1;">emergency_home</span>
                </button>
                <div class="mt-4 text-center">
                  <h2 class="font-display-lg text-display-lg font-bold text-primary">BOTÓN DE PÁNICO</h2>
                  <p class="font-body-md text-body-md text-secondary mt-1">Presiona si tuviste un accidente</p>
                </div>
              </section>
              <!-- Section: Document Status (Horizontal Scroll) -->
              <section>
                <div class="flex justify-between items-center mb-4">
                  <h3 class="font-headline-sm text-headline-sm">Estado de tus documentos</h3>
                  <button class="text-primary font-label-lg text-label-lg">Ver todo</button>
                </div>
                <div class="flex gap-4 overflow-x-auto hide-scrollbar -mx-margin-mobile px-margin-mobile pb-4">
                  <!-- SOAT -->
                  <div
                    class="min-w-[200px] bg-surface-container-lowest p-5 rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.08)] border-l-4 border-[#27AE60]">
                    <div class="flex justify-between items-start mb-3">
                      <span class="material-symbols-outlined text-secondary" data-icon="description">description</span>
                      <span
                        class="bg-[#27AE60]/15 text-[#27AE60] text-[10px] font-bold px-2 py-1 rounded-full">VIGENTE</span>
                    </div>
                    <p class="font-label-lg text-label-lg text-on-surface">SOAT</p>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Vence: 12 Nov 2024</p>
                  </div>
                  <!-- Tecnomecánica -->
                  <div
                    class="min-w-[200px] bg-surface-container-lowest p-5 rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.08)] border-l-4 border-[#F5A623]">
                    <div class="flex justify-between items-start mb-3">
                      <span class="material-symbols-outlined text-secondary"
                        data-icon="build_circle">build_circle</span>
                      <span
                        class="bg-[#F5A623]/15 text-[#F5A623] text-[10px] font-bold px-2 py-1 rounded-full uppercase">Vence
                        pronto</span>
                    </div>
                    <p class="font-label-lg text-label-lg text-on-surface uppercase">Tecnomecánica</p>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Vence: 15 Oct 2023</p>
                  </div>
                  <!-- Licencia -->
                  <div
                    class="min-w-[200px] bg-surface-container-lowest p-5 rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.08)] border-l-4 border-[#27AE60]">
                    <div class="flex justify-between items-start mb-3">
                      <span class="material-symbols-outlined text-secondary" data-icon="badge">badge</span>
                      <span
                        class="bg-[#27AE60]/15 text-[#27AE60] text-[10px] font-bold px-2 py-1 rounded-full">VIGENTE</span>
                    </div>
                    <p class="font-label-lg text-label-lg text-on-surface">Licencia</p>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Vence: 05 Ene 2028</p>
                  </div>
                </div>
              </section>
              <!-- Section: Mantenimiento (2x2 Grid) -->
              <section>
                <h3 class="font-headline-sm text-headline-sm mb-4">Mantenimiento</h3>
                <div class="grid grid-cols-2 gap-4">
                  <div
                    class="bg-surface-container-low p-4 rounded-xl flex flex-col items-center justify-center h-[120px] text-center border border-outline-variant/30 transition-transform active:scale-95">
                    <span class="material-symbols-outlined text-primary mb-2" data-icon="tire_repair">tire_repair</span>
                    <span class="font-label-lg text-label-lg text-on-surface">Llantas</span>
                    <span class="text-[10px] text-on-surface-variant">Revisar presión</span>
                  </div>
                  <div
                    class="bg-surface-container-low p-4 rounded-xl flex flex-col items-center justify-center h-[120px] text-center border border-outline-variant/30 transition-transform active:scale-95">
                    <span class="material-symbols-outlined text-primary mb-2"
                      data-icon="battery_charging_full">battery_charging_full</span>
                    <span class="font-label-lg text-label-lg text-on-surface">Batería</span>
                    <span class="text-[10px] text-on-surface-variant">85% Salud</span>
                  </div>
                  <div
                    class="bg-surface-container-low p-4 rounded-xl flex flex-col items-center justify-center h-[120px] text-center border border-outline-variant/30 transition-transform active:scale-95">
                    <span class="material-symbols-outlined text-primary mb-2"
                      data-icon="settings_suggest">settings_suggest</span>
                    <span class="font-label-lg text-label-lg text-on-surface">Servicio</span>
                    <span class="text-[10px] text-on-surface-variant">En 2.500 km</span>
                  </div>
                  <div
                    class="bg-surface-container-low p-4 rounded-xl flex flex-col items-center justify-center h-[120px] text-center border border-outline-variant/30 transition-transform active:scale-95">
                    <span class="material-symbols-outlined text-primary mb-2" data-icon="oil_barrel">oil_barrel</span>
                    <span class="font-label-lg text-label-lg text-on-surface">Aceite</span>
                    <span class="text-[10px] text-on-surface-variant">Nivel Óptimo</span>
                  </div>
                </div>
              </section>
              <!-- Section: Incidentes recientes -->
              <section>
                <h3 class="font-headline-sm text-headline-sm mb-4">Incidentes recientes</h3>
                <div class="space-y-4">
                  <div
                    class="bg-surface-container-lowest p-4 rounded-xl shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-error-container/20 flex items-center justify-center">
                      <span class="material-symbols-outlined text-error" data-icon="car_crash">car_crash</span>
                    </div>
                    <div class="flex-1">
                      <p class="font-label-lg text-label-lg text-on-surface">Choque simple - Calle 100</p>
                      <p class="font-body-sm text-body-sm text-on-surface-variant">24 Ago 2023 • Finalizado</p>
                    </div>
                    <span class="material-symbols-outlined text-outline" data-icon="chevron_right">chevron_right</span>
                  </div>
                  <div
                    class="bg-surface-container-lowest p-4 rounded-xl shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-surface-container-high flex items-center justify-center">
                      <span class="material-symbols-outlined text-secondary" data-icon="minor_crash">minor_crash</span>
                    </div>
                    <div class="flex-1">
                      <p class="font-label-lg text-label-lg text-on-surface">Rayón parqueadero</p>
                      <p class="font-body-sm text-body-sm text-on-surface-variant">10 Jun 2023 • Reportado</p>
                    </div>
                    <span class="material-symbols-outlined text-outline" data-icon="chevron_right">chevron_right</span>
                  </div>
                </div>
              </section>
            </main>
          </body>

          </html>
        </div>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- ╔══════════════════════════════════════════╗ -->
  <!-- ║  PROBLEMÁTICA                            ║ -->
  <!-- ╚══════════════════════════════════════════╝ -->
  <section class="section problema" id="problema">
    <div class="container">
      <div class="section-eyebrow fade-in">
        <div class="eyebrow-line"></div>
        <span>La problemática</span>
      </div>
      <h2
        style="font:var(--display-lg);letter-spacing:-0.02em;color:var(--on-surface);max-width:600px;margin-bottom:12px;"
        class="fade-in">
        Un accidente desborda cualquier preparación previa
      </h2>
      <p style="font:var(--body-lg);color:var(--on-surface-variant);max-width:560px;" class="fade-in">
        El impacto emocional de un choque activa respuestas de estrés agudo que bloquean la toma de decisiones
        racionales. Las personas quedan paralizadas en el momento en que más necesitan actuar.
      </p>

      <div class="problema-grid stagger">
        <div class="card problema-card fade-in" style="--i:0">
          <div class="icon-wrap icon-wrap-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
          </div>
          <h3 class="problema-card-title">El estrés anula la razón</h3>
          <p class="problema-card-body">La adrenalina y el cortisol bloquean el pensamiento racional en segundos. Los
            protocolos conocidos se evaporan y la persona entra en modo de supervivencia sin saber por dónde empezar.
          </p>
        </div>
        <div class="card problema-card fade-in" style="--i:1">
          <div class="icon-wrap icon-wrap-navy">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <polyline points="14 2 14 8 20 8" />
              <line x1="16" y1="13" x2="8" y2="13" />
              <line x1="16" y1="17" x2="8" y2="17" />
              <polyline points="10 9 9 9 8 9" />
            </svg>
          </div>
          <h3 class="problema-card-title">Desconocimiento del protocolo</h3>
          <p class="problema-card-body">La mayoría de conductores no saben qué datos recolectar, en qué orden actuar ni
            qué derechos tienen. Esto genera conflictos con la contraparte y retrasos con la aseguradora.</p>
        </div>
        <div class="card problema-card fade-in" style="--i:2">
          <div class="icon-wrap icon-wrap-amber">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
              <circle cx="12" cy="13" r="4" />
            </svg>
          </div>
          <h3 class="problema-card-title">Evidencia mal registrada</h3>
          <p class="problema-card-body">Sin guía, se pierden fotografías críticas, se olvida registrar placas o testigos
            y la escena se altera antes de ser documentada. Una oportunidad única e irrecuperable.</p>
        </div>
        <div class="card problema-card fade-in" style="--i:3">
          <div class="icon-wrap icon-wrap-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
              <line x1="8" y1="21" x2="16" y2="21" />
              <line x1="12" y1="17" x2="12" y2="21" />
            </svg>
          </div>
          <h3 class="problema-card-title">Reportes incompletos</h3>
          <p class="problema-card-body">Un reporte deficiente puede retrasar indemnizaciones, generar rechazos parciales
            o poner en desventaja al asegurado. El proceso de reclamación exige precisión desde el primer momento.</p>
        </div>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- ╔══════════════════════════════════════════╗ -->
  <!-- ║  SOLUCIÓN / PASOS                        ║ -->
  <!-- ╚══════════════════════════════════════════╝ -->
  <section class="section solucion" id="solucion">
    <div class="container">
      <div class="solucion-inner">
        <div>
          <div class="section-eyebrow fade-in">
            <div class="eyebrow-line"></div>
            <span>La solución</span>
          </div>
          <h2 style="font:var(--display-lg);letter-spacing:-0.02em;color:var(--on-surface);margin-bottom:12px;"
            class="fade-in">
            Un botón. Una guía.<br>Toda la calma que necesitas.
          </h2>
          <p style="font:var(--body-md);color:var(--on-surface-variant);" class="fade-in">
            Al presionar el botón de emergencia, ChocApp toma el control y te lleva paso a paso, eliminando la parálisis
            en el momento más crítico.
          </p>

          <div class="pasos-list">
            <div class="paso-item fade-in">
              <div class="step-dot active">1</div>
              <div class="paso-content">
                <div class="paso-title">Activa el modo emergencia</div>
                <div class="paso-desc">Un solo toque despliega la guía y registra timestamp y ubicación GPS del
                  incidente.</div>
              </div>
            </div>
            <div class="paso-item fade-in">
              <div class="step-dot">2</div>
              <div class="paso-content">
                <div class="paso-title">Verifica tu seguridad</div>
                <div class="paso-desc">El primer paso siempre es asegurar el bienestar físico de todos los involucrados.
                </div>
              </div>
            </div>
            <div class="paso-item fade-in">
              <div class="step-dot">3</div>
              <div class="paso-content">
                <div class="paso-title">Registra datos del incidente</div>
                <div class="paso-desc">La app te guía para capturar placas, datos del otro conductor, aseguradora y
                  testigos.</div>
              </div>
            </div>
            <div class="paso-item fade-in">
              <div class="step-dot">4</div>
              <div class="paso-content">
                <div class="paso-title">Documenta con fotografías</div>
                <div class="paso-desc">Instrucciones específicas sobre qué fotografiar y desde qué ángulos para
                  evidencia sólida.</div>
              </div>
            </div>
            <div class="paso-item fade-in">
              <div class="step-dot">5</div>
              <div class="paso-content">
                <div class="paso-title">Genera el reporte PDF</div>
                <div class="paso-desc">ChocApp ensambla automáticamente el documento completo listo para entregar.</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel visual -->
        <div class="paso-panel fade-in">
          <!DOCTYPE html>

          <html lang="es">

          <head>
            <meta charset="utf-8" />
            <meta content="width=device-width, initial-scale=1.0" name="viewport" />
            <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;display=swap"
              rel="stylesheet" />
            <link
              href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
              rel="stylesheet" />
            <link
              href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
              rel="stylesheet" />
            <script id="tailwind-config">
              tailwind.config = {
                darkMode: "class",
                theme: {
                  extend: {
                    "colors": {
                      "surface-container-lowest": "#ffffff",
                      "on-primary-container": "#fffbff",
                      "on-primary": "#ffffff",
                      "surface-bright": "#f8f9fa",
                      "on-tertiary-container": "#fffbff",
                      "secondary-fixed": "#e2e0fc",
                      "on-tertiary-fixed-variant": "#633f00",
                      "surface-container": "#edeeef",
                      "surface-tint": "#bf0811",
                      "on-secondary-fixed-variant": "#45455b",
                      "on-error": "#ffffff",
                      "on-tertiary": "#ffffff",
                      "on-background": "#191c1d",
                      "outline": "#916f6b",
                      "primary": "#bb020f",
                      "surface-container-low": "#f3f4f5",
                      "outline-variant": "#e6bdb8",
                      "secondary": "#5d5c74",
                      "inverse-on-surface": "#f0f1f2",
                      "on-secondary-fixed": "#1a1a2e",
                      "inverse-primary": "#ffb4aa",
                      "on-surface": "#191c1d",
                      "tertiary": "#805200",
                      "on-secondary": "#ffffff",
                      "primary-container": "#e02a25",
                      "error-container": "#ffdad6",
                      "secondary-fixed-dim": "#c6c4df",
                      "tertiary-fixed": "#ffddb4",
                      "background": "#f8f9fa",
                      "inverse-surface": "#2e3132",
                      "surface-variant": "#e1e3e4",
                      "on-surface-variant": "#5c403c",
                      "surface-container-highest": "#e1e3e4",
                      "surface-container-high": "#e7e8e9",
                      "on-primary-fixed": "#410001",
                      "on-tertiary-fixed": "#291800",
                      "secondary-container": "#e2e0fc",
                      "error": "#ba1a1a",
                      "on-secondary-container": "#63627a",
                      "tertiary-fixed-dim": "#ffb955",
                      "surface-dim": "#d9dadb",
                      "surface": "#f8f9fa",
                      "tertiary-container": "#a16900",
                      "on-primary-fixed-variant": "#930008",
                      "primary-fixed": "#ffdad5",
                      "primary-fixed-dim": "#ffb4aa",
                      "on-error-container": "#93000a"
                    },
                    "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "full": "9999px"
                    },
                    "spacing": {
                      "margin-mobile": "20px",
                      "container-padding": "16px",
                      "unit": "4px",
                      "tap-target-min": "56px",
                      "gutter": "16px"
                    },
                    "fontFamily": {
                      "headline-sm": ["Inter"],
                      "display-lg": ["Inter"],
                      "body-sm": ["Inter"],
                      "headline-md": ["Inter"],
                      "label-md": ["Inter"],
                      "body-lg": ["Inter"],
                      "body-md": ["Inter"],
                      "label-lg": ["Inter"]
                    },
                    "fontSize": {
                      "headline-sm": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                      "display-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                      "body-sm": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                      "headline-md": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
                      "label-md": ["12px", { "lineHeight": "16px", "letterSpacing": "0.02em", "fontWeight": "600" }],
                      "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                      "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                      "label-lg": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "600" }]
                    }
                  },
                },
              }
            </script>
            <style>
              .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
              }

              .hotspot-ring {
                animation: pulse-ring 2s cubic-bezier(0.455, 0.03, 0.515, 0.955) infinite;
              }

              @keyframes pulse-ring {
                0% {
                  transform: scale(.8);
                  opacity: 0.5;
                }

                50% {
                  transform: scale(1.2);
                  opacity: 0;
                }

                100% {
                  transform: scale(.8);
                  opacity: 0;
                }
              }

              .car-bg-pattern {
                background-color: #f8f9fa;
                background-image: radial-gradient(#d1d5db 0.5px, transparent 0.5px);
                background-size: 16px 16px;
              }
            </style>
            <style>
              body {
                min-height: max(884px, 100dvh);
              }
            </style>
          </head>

          <body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
            <!-- Top Navigation Bar -->
            <header
              class="flex justify-between items-center px-margin-mobile h-tap-target-min w-full z-50 bg-surface fixed top-0">
              <div class="flex items-center gap-4">
                <button aria-label="Volver"
                  class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-surface-container-low transition-colors">
                  <span class="material-symbols-outlined text-on-surface">arrow_back</span>
                </button>
                <h1 class="font-headline-sm text-headline-sm text-on-surface">Registro de Choque</h1>
              </div>
              <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center">
                <span class="material-symbols-outlined text-secondary">help</span>
              </div>
            </header>
            <!-- Content Canvas -->
            <main class="flex-grow pt-tap-target-min pb-32 px-margin-mobile max-w-md mx-auto w-full">
              <!-- Step Indicator & Title -->
              <section class="mt-8 mb-6">
                <div class="flex justify-between items-end mb-2">
                  <h2 class="font-display-lg text-display-lg text-primary">Fotografía tu vehículo</h2>
                  <span class="font-label-lg text-label-lg text-secondary mb-1">Paso 2 de 4</span>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant">Captura los 8 ángulos obligatorios para el
                  reporte oficial de la aseguradora.</p>
              </section>
              <!-- Progress Overview Card -->
              <div class="bg-surface-container-lowest rounded-xl p-4 shadow-sm mb-8 border border-outline-variant/30">
                <div class="flex items-center justify-between mb-3">
                  <span class="font-label-lg text-label-lg text-on-surface">3 de 8 ángulos fotografiados</span>
                  <span class="font-label-md text-label-md text-primary font-bold">37%</span>
                </div>
                <div class="w-full bg-surface-container-high h-2 rounded-full overflow-hidden">
                  <div class="bg-primary h-full transition-all duration-500" style="width: 37.5%"></div>
                </div>
              </div>
              <!-- Car Illustration with Hotspots -->
              <div
                class="relative aspect-[3/4] w-full car-bg-pattern rounded-3xl border border-outline-variant/50 overflow-hidden mb-8 flex items-center justify-center">
                <!-- Main Car Illustration -->
                <img alt="Vehículo diagrama superior"
                  class="w-48 h-auto opacity-40 rotate-180 object-contain mix-blend-multiply"
                  data-alt="A clean, minimalist top-down architectural technical illustration of a generic modern sedan car, isolated on a light gray background. The lighting is perfectly flat and clinical, consistent with a professional insurance claim application. The aesthetic is corporate modern with high-contrast sharp edges, using a neutral palette with slight red accents. The car is positioned vertically to allow for interactive hotspots around its perimeter."
                  src="https://lh3.googleusercontent.com/aida-public/AB6AXuDztzaEScVAwdebL_rAXrt4-n_d305jmroRogzMMvvVXbcEkdz12EztdgiQzXwOBJc_7Lp495W6OOGQegl1Ist-l-S9-I-fvTnmylEm7VjSLz3rNUzucgEE-VHCsEEnx6THDdaq5hVbNwjgRvTrXi0CAXKcRFKGraQ_5wcaqMIRokAvnhlL9cuDjQB-9YaRG4l7_DrPkXHx_6hjcDK-vw0ar_VSIcHAJ5IqdXCB0lSKCqoTOJRCv_GiGWoxEpgkIDxCcNtpJcKRVHk" />
                <!-- Hotspots Container -->
                <div class="absolute inset-0 pointer-events-none">
                  <!-- 1. Frente (Completed) -->
                  <button
                    class="absolute top-[10%] left-1/2 -translate-x-1/2 pointer-events-auto flex flex-col items-center group">
                    <div
                      class="w-10 h-10 bg-on-tertiary-fixed-variant/10 rounded-full border-2 border-on-tertiary-fixed-variant flex items-center justify-center shadow-md bg-white">
                      <span class="material-symbols-outlined text-[#27AE60]"
                        style="font-variation-settings: 'wght' 700">check</span>
                    </div>
                    <span
                      class="mt-1 font-label-md text-label-md bg-white/90 px-2 py-0.5 rounded shadow-sm text-secondary">Frente</span>
                  </button>
                  <!-- 2. Frente 45 Izq (Pending) -->
                  <button class="absolute top-[20%] left-[15%] pointer-events-auto flex flex-col items-center group">
                    <div
                      class="relative w-10 h-10 bg-white rounded-full border-2 border-outline flex items-center justify-center shadow-md hover:border-primary transition-colors">
                      <div class="absolute inset-0 rounded-full border-2 border-primary/30 hotspot-ring"></div>
                      <span class="material-symbols-outlined text-outline group-hover:text-primary">photo_camera</span>
                    </div>
                    <span
                      class="mt-1 font-label-md text-label-md bg-white/90 px-2 py-0.5 rounded shadow-sm text-secondary">Frontal
                      Izq</span>
                  </button>
                  <!-- 3. Frente 45 Der (Completed) -->
                  <button class="absolute top-[20%] right-[15%] pointer-events-auto flex flex-col items-center group">
                    <div
                      class="w-10 h-10 bg-white rounded-full border-2 border-on-tertiary-fixed-variant flex items-center justify-center shadow-md">
                      <span class="material-symbols-outlined text-[#27AE60]"
                        style="font-variation-settings: 'wght' 700">check</span>
                    </div>
                    <span
                      class="mt-1 font-label-md text-label-md bg-white/90 px-2 py-0.5 rounded shadow-sm text-secondary">Frontal
                      Der</span>
                  </button>
                  <!-- 4. Lado Izquierdo (Pending) -->
                  <button
                    class="absolute top-1/2 left-[5%] -translate-y-1/2 pointer-events-auto flex flex-col items-center group">
                    <div
                      class="w-10 h-10 bg-white rounded-full border-2 border-outline flex items-center justify-center shadow-md hover:border-primary transition-colors">
                      <span class="material-symbols-outlined text-outline group-hover:text-primary">photo_camera</span>
                    </div>
                    <span
                      class="mt-1 font-label-md text-label-md bg-white/90 px-2 py-0.5 rounded shadow-sm text-secondary">Lado
                      Izq</span>
                  </button>
                  <!-- 5. Lado Derecho (Pending) -->
                  <button
                    class="absolute top-1/2 right-[5%] -translate-y-1/2 pointer-events-auto flex flex-col items-center group">
                    <div
                      class="w-10 h-10 bg-white rounded-full border-2 border-outline flex items-center justify-center shadow-md hover:border-primary transition-colors">
                      <span class="material-symbols-outlined text-outline group-hover:text-primary">photo_camera</span>
                    </div>
                    <span
                      class="mt-1 font-label-md text-label-md bg-white/90 px-2 py-0.5 rounded shadow-sm text-secondary">Lado
                      Der</span>
                  </button>
                  <!-- 6. Trasero 45 Izq (Pending) -->
                  <button class="absolute bottom-[20%] left-[15%] pointer-events-auto flex flex-col items-center group">
                    <div
                      class="w-10 h-10 bg-white rounded-full border-2 border-outline flex items-center justify-center shadow-md hover:border-primary transition-colors">
                      <span class="material-symbols-outlined text-outline group-hover:text-primary">photo_camera</span>
                    </div>
                    <span
                      class="mt-1 font-label-md text-label-md bg-white/90 px-2 py-0.5 rounded shadow-sm text-secondary">Trasero
                      Izq</span>
                  </button>
                  <!-- 7. Trasero 45 Der (Pending) -->
                  <button
                    class="absolute bottom-[20%] right-[15%] pointer-events-auto flex flex-col items-center group">
                    <div
                      class="w-10 h-10 bg-white rounded-full border-2 border-outline flex items-center justify-center shadow-md hover:border-primary transition-colors">
                      <span class="material-symbols-outlined text-outline group-hover:text-primary">photo_camera</span>
                    </div>
                    <span
                      class="mt-1 font-label-md text-label-md bg-white/90 px-2 py-0.5 rounded shadow-sm text-secondary">Trasero
                      Der</span>
                  </button>
                  <!-- 8. Trasero (Completed) -->
                  <button
                    class="absolute bottom-[10%] left-1/2 -translate-x-1/2 pointer-events-auto flex flex-col items-center group">
                    <div
                      class="w-10 h-10 bg-white rounded-full border-2 border-on-tertiary-fixed-variant flex items-center justify-center shadow-md">
                      <span class="material-symbols-outlined text-[#27AE60]"
                        style="font-variation-settings: 'wght' 700">check</span>
                    </div>
                    <span
                      class="mt-1 font-label-md text-label-md bg-white/90 px-2 py-0.5 rounded shadow-sm text-secondary">Trasero</span>
                  </button>
                </div>
              </div>
              <!-- Instructions Section -->
              <section class="space-y-4">
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Instrucciones de cumplimiento</h3>
                <div class="grid grid-cols-1 gap-3">
                  <div class="flex gap-4 p-4 bg-surface-container-low rounded-xl border border-outline-variant/20">
                    <span class="material-symbols-outlined text-primary">lightbulb</span>
                    <div class="flex-1">
                      <p class="font-label-lg text-label-lg text-on-surface">Iluminación Adecuada</p>
                      <p class="font-body-sm text-body-sm text-on-surface-variant">Asegúrate de que la placa sea visible
                        y no haya sombras fuertes cubriendo el daño.</p>
                    </div>
                  </div>
                  <div class="flex gap-4 p-4 bg-surface-container-low rounded-xl border border-outline-variant/20">
                    <span class="material-symbols-outlined text-primary">distance</span>
                    <div class="flex-1">
                      <p class="font-label-lg text-label-lg text-on-surface">Distancia Recomendada</p>
                      <p class="font-body-sm text-body-sm text-on-surface-variant">Mantente a 2-3 metros del vehículo
                        para capturar el contexto del entorno.</p>
                    </div>
                  </div>
                </div>
              </section>
            </main>
          </body>

          </html>
        </div>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- ╔══════════════════════════════════════════╗ -->
  <!-- ║  BENEFICIOS                              ║ -->
  <!-- ╚══════════════════════════════════════════╝ -->
  <section class="section beneficios" id="beneficios">
    <div class="container">
      <div class="section-eyebrow fade-in">
        <div class="eyebrow-line"></div>
        <span>Beneficios clave</span>
      </div>
      <h2
        style="font:var(--display-lg);letter-spacing:-0.02em;color:var(--on-surface);max-width:560px;margin-bottom:12px;"
        class="fade-in">
        Por qué ChocApp cambia la experiencia de un accidente
      </h2>
      <p style="font:var(--body-lg);color:var(--on-surface-variant);max-width:520px;" class="fade-in">
        Más allá de ser una guía, ChocApp es una herramienta de empoderamiento que convierte el caos en un proceso
        ordenado.
      </p>

      <div class="beneficios-grid stagger">
        <div class="card beneficio-card fade-in" style="--i:0">
          <div class="icon-wrap icon-wrap-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
            </svg>
          </div>
          <h3 class="beneficio-title">Respuesta inmediata</h3>
          <p class="beneficio-body">Sin esperar llamadas ni buscar instrucciones. La guía se activa en segundos desde el
            momento del impacto.</p>
        </div>
        <div class="card beneficio-card fade-in" style="--i:1">
          <div class="icon-wrap icon-wrap-navy">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10" />
              <polyline points="12 6 12 12 16 14" />
            </svg>
          </div>
          <h3 class="beneficio-title">Claridad en el caos</h3>
          <p class="beneficio-body">El flujo paso a paso elimina la parálisis por decisión. Sabes exactamente qué hacer
            a continuación.</p>
        </div>
        <div class="card beneficio-card fade-in" style="--i:2">
          <div class="icon-wrap icon-wrap-amber">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21.21 15.89A10 10 0 1 1 8 2.83" />
              <path d="M22 12A10 10 0 0 0 12 2v10z" />
            </svg>
          </div>
          <h3 class="beneficio-title">Evidencia completa</h3>
          <p class="beneficio-body">Ningún dato crítico se omite: fotos, placas, testigos y condiciones quedan
            registrados con precisión.</p>
        </div>
        <div class="card beneficio-card fade-in" style="--i:3">
          <div class="icon-wrap icon-wrap-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
          </div>
          <h3 class="beneficio-title">Proceso ágil de reclamación</h3>
          <p class="beneficio-body">El reporte cumple los requisitos estándar de las aseguradoras colombianas,
            reduciendo tiempos y rechazos.</p>
        </div>
        <div class="card beneficio-card fade-in" style="--i:4">
          <div class="icon-wrap icon-wrap-navy">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <polyline points="14 2 14 8 20 8" />
              <line x1="16" y1="13" x2="8" y2="13" />
              <line x1="16" y1="17" x2="8" y2="17" />
              <polyline points="10 9 9 9 8 9" />
            </svg>
          </div>
          <h3 class="beneficio-title">Protección legal</h3>
          <p class="beneficio-body">La documentación correcta desde el inicio protege al usuario ante disputas legales o
            diferencias con la contraparte.</p>
        </div>
        <div class="card beneficio-card fade-in" style="--i:5">
          <div class="icon-wrap icon-wrap-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
              <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
          </div>
          <h3 class="beneficio-title">Accesible para todos</h3>
          <p class="beneficio-body">Interfaz diseñada para operar bajo estrés. Botones de 56px, jerarquía clara, sin
            conocimiento técnico requerido.</p>
        </div>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- ╔══════════════════════════════════════════╗ -->
  <!-- ║  PÚBLICO OBJETIVO                        ║ -->
  <!-- ╚══════════════════════════════════════════╝ -->
  <section class="section publico">
    <div class="container">
      <div class="section-eyebrow fade-in">
        <div class="eyebrow-line"></div>
        <span>Público objetivo</span>
      </div>
      <h2 class="publico-title fade-in">¿Para quién es ChocApp?</h2>
      <p class="publico-subtitle fade-in">Conductores activos en Colombia que necesitan seguridad, claridad y respaldo
        documental en el peor momento.</p>

      <div class="publico-grid stagger">
        <div class="publico-card fade-in" style="--i:0">
          <div class="icon-wrap"
            style="background:rgba(232,48,42,0.15);color:var(--inverse-primary);width:44px;height:44px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
          </div>
          <h3 class="publico-card-title">Conductor particular</h3>
          <p class="publico-card-body">Vehículo propio asegurado. Necesita proteger sus derechos frente a la aseguradora
            y la contraparte sin conocer el protocolo oficial.</p>
        </div>
        <div class="publico-card fade-in" style="--i:1">
          <div class="icon-wrap"
            style="background:rgba(232,48,42,0.15);color:var(--inverse-primary);width:44px;height:44px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="1" y="3" width="15" height="13" />
              <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
              <circle cx="5.5" cy="18.5" r="2.5" />
              <circle cx="18.5" cy="18.5" r="2.5" />
            </svg>
          </div>
          <h3 class="publico-card-title">Conductor de uso profesional</h3>
          <p class="publico-card-body">Representantes comerciales, mensajeros o trabajadores para quienes el vehículo es
            herramienta de trabajo y necesitan soluciones rápidas.</p>
        </div>
        <div class="publico-card fade-in" style="--i:2">
          <div class="icon-wrap"
            style="background:rgba(232,48,42,0.15);color:var(--inverse-primary);width:44px;height:44px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="8" x2="12" y2="12" />
              <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
          </div>
          <h3 class="publico-card-title">Sin experiencia en seguros</h3>
          <p class="publico-card-body">Personas que enfrentan su primer accidente sin saber por dónde comenzar ni qué
            información es legalmente relevante para la reclamación.</p>
        </div>
        <div class="publico-card fade-in" style="--i:3">
          <div class="icon-wrap"
            style="background:rgba(232,48,42,0.15);color:var(--inverse-primary);width:44px;height:44px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
              <line x1="12" y1="18" x2="12.01" y2="18" />
            </svg>
          </div>
          <h3 class="publico-card-title">Usuario Android +18</h3>
          <p class="publico-card-body">Cualquier conductor mayor de edad con smartphone Android. La interfaz opera bajo
            estrés sin requerir conocimientos técnicos previos.</p>
        </div>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- ╔══════════════════════════════════════════╗ -->
  <!-- ║  REPORTE                                 ║ -->
  <!-- ╚══════════════════════════════════════════╝ -->
  <section class="section reporte" id="reporte">
    <div class="container">
      <div class="reporte-inner">
        <div>
          <div class="section-eyebrow fade-in">
            <div class="eyebrow-line"></div>
            <span>El reporte inteligente</span>
          </div>
          <h2 style="font:var(--display-lg);letter-spacing:-0.02em;color:var(--on-surface);margin-bottom:12px;"
            class="fade-in">
            De accidente a documento en minutos
          </h2>
          <p style="font:var(--body-md);color:var(--on-surface-variant);" class="fade-in">
            ChocApp recopila todo durante el flujo guiado y genera automáticamente un reporte profesional listo para la
            aseguradora, sin que el conductor deba recordar nada después.
          </p>

          <div class="feature-list stagger">
            <div class="feature-row fade-in" style="--i:0">
              <div class="feature-check">✓</div>
              <p class="feature-text"><strong>Formulario estructurado</strong> con todos los campos requeridos por
                aseguradoras colombianas.</p>
            </div>
            <div class="feature-row fade-in" style="--i:1">
              <div class="feature-check">✓</div>
              <p class="feature-text"><strong>Evidencia fotográfica</strong> organizada con metadatos de hora y
                ubicación GPS.</p>
            </div>
            <div class="feature-row fade-in" style="--i:2">
              <div class="feature-check">✓</div>
              <p class="feature-text"><strong>Datos de partes involucradas:</strong> conductor, vehículo, placa,
                aseguradora y testigos.</p>
            </div>
            <div class="feature-row fade-in" style="--i:3">
              <div class="feature-check">✓</div>
              <p class="feature-text"><strong>Exportación en PDF</strong> lista para compartir por correo, WhatsApp o
                presencialmente.</p>
            </div>
            <div class="feature-row fade-in" style="--i:4">
              <div class="feature-check">✓</div>
              <p class="feature-text"><strong>Almacenamiento offline</strong> del reporte para consulta sin necesidad de
                internet.</p>
            </div>
            <div class="feature-row fade-in" style="--i:5">
              <div class="feature-check">✓</div>
              <p class="feature-text"><strong>Sincronización en nube</strong> (Firebase) para respaldo seguro cuando hay
                conexión disponible.</p>
            </div>
          </div>
        </div>

        <!-- PDF Mockup -->
        <div class="fade-in">
          <!DOCTYPE html>

          <html class="light" lang="es">

          <head>
            <meta charset="utf-8" />
            <meta content="width=device-width, initial-scale=1.0" name="viewport" />
            <title>Detalle de Accidente - ChocApp</title>
            <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&amp;display=swap"
              rel="stylesheet" />
            <link
              href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
              rel="stylesheet" />
            <link
              href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
              rel="stylesheet" />
            <script id="tailwind-config">
              tailwind.config = {
                darkMode: "class",
                theme: {
                  extend: {
                    "colors": {
                      "surface-container-lowest": "#ffffff",
                      "on-primary-container": "#fffbff",
                      "on-primary": "#ffffff",
                      "surface-bright": "#f8f9fa",
                      "on-tertiary-container": "#fffbff",
                      "secondary-fixed": "#e2e0fc",
                      "on-tertiary-fixed-variant": "#633f00",
                      "surface-container": "#edeeef",
                      "surface-tint": "#bf0811",
                      "on-secondary-fixed-variant": "#45455b",
                      "on-error": "#ffffff",
                      "on-tertiary": "#ffffff",
                      "on-background": "#191c1d",
                      "outline": "#916f6b",
                      "primary": "#bb020f",
                      "surface-container-low": "#f3f4f5",
                      "outline-variant": "#e6bdb8",
                      "secondary": "#5d5c74",
                      "inverse-on-surface": "#f0f1f2",
                      "on-secondary-fixed": "#1a1a2e",
                      "inverse-primary": "#ffb4aa",
                      "on-surface": "#191c1d",
                      "tertiary": "#805200",
                      "on-secondary": "#ffffff",
                      "primary-container": "#e02a25",
                      "error-container": "#ffdad6",
                      "secondary-fixed-dim": "#c6c4df",
                      "tertiary-fixed": "#ffddb4",
                      "background": "#f8f9fa",
                      "inverse-surface": "#2e3132",
                      "surface-variant": "#e1e3e4",
                      "on-surface-variant": "#5c403c",
                      "surface-container-highest": "#e1e3e4",
                      "surface-container-high": "#e7e8e9",
                      "on-primary-fixed": "#410001",
                      "on-tertiary-fixed": "#291800",
                      "secondary-container": "#e2e0fc",
                      "error": "#ba1a1a",
                      "on-secondary-container": "#63627a",
                      "tertiary-fixed-dim": "#ffb955",
                      "surface-dim": "#d9dadb",
                      "surface": "#f8f9fa",
                      "tertiary-container": "#a16900",
                      "on-primary-fixed-variant": "#930008",
                      "primary-fixed": "#ffdad5",
                      "primary-fixed-dim": "#ffb4aa",
                      "on-error-container": "#93000a"
                    },
                    "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "full": "9999px"
                    },
                    "spacing": {
                      "margin-mobile": "20px",
                      "container-padding": "16px",
                      "unit": "4px",
                      "tap-target-min": "56px",
                      "gutter": "16px"
                    },
                    "fontFamily": {
                      "headline-sm": ["Inter"],
                      "display-lg": ["Inter"],
                      "body-sm": ["Inter"],
                      "headline-md": ["Inter"],
                      "label-md": ["Inter"],
                      "body-lg": ["Inter"],
                      "body-md": ["Inter"],
                      "label-lg": ["Inter"]
                    },
                    "fontSize": {
                      "headline-sm": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                      "display-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                      "body-sm": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                      "headline-md": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
                      "label-md": ["12px", { "lineHeight": "16px", "letterSpacing": "0.02em", "fontWeight": "600" }],
                      "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                      "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                      "label-lg": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "600" }]
                    }
                  },
                },
              }
            </script>
            <style>
              .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
              }

              .hide-scrollbar::-webkit-scrollbar {
                display: none;
              }

              .hide-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
              }
            </style>
            <style>
              body {
                min-height: max(884px, 100dvh);
              }
            </style>
          </head>

          <body class="bg-surface text-on-surface font-body-md selection:bg-primary-fixed">
            <!-- Top Navigation -->
            <header
              class="flex justify-between items-center px-margin-mobile h-tap-target-min w-full z-50 bg-surface dark:bg-surface sticky top-0">
              <div class="flex items-center gap-3">
                <button aria-label="Volver"
                  class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-surface-container-low transition-colors text-primary">
                  <span class="material-symbols-outlined">arrow_back</span>
                </button>
                <h1 class="font-headline-sm text-headline-sm text-primary">Accidente</h1>
              </div>
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary" data-icon="notifications">notifications</span>
                <div
                  class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center overflow-hidden border border-outline-variant">
                  <img alt="User Profile" class="w-full h-full object-cover"
                    data-alt="A high-quality professional studio portrait of a man in his 30s with a confident expression, set against a clean minimalist light gray background. The lighting is soft and directional, emphasizing natural skin textures and a clean-shaven look. He is wearing a simple dark navy blue polo shirt. The overall image style is polished, corporate, and trustworthy, perfectly aligning with a modern insurance application's professional aesthetic."
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuBLFzldGgxvwX9GTJie9Wdxo13o04zkcRYzPpBLqm-zkQbs_tPp15dGGirnI4X5PctFl4zNTgqZTdMyVFo1FVKAm62iQvPpC81Dw_-xhtsEwCRgMHul0m4reiH5iTOD2YhaIDK9PWbK7gPJcNhcUUmf-w5hxKJ7wsUHCvkgp4CRyZqX6vDp_pQzRL1qjX0xDd40KG6N48WPmYXRAo-Ou2G2vtFm6UkdX5ftTi4M-fubTkL5QmWRSyaGyDYGLphuuKweOZCeDgxsteY" />
                </div>
              </div>
            </header>
            <main class="pb-32">
              <!-- Hero Information Section -->
              <section class="px-margin-mobile pt-6 pb-4">
                <div class="flex flex-col gap-2">
                  <span
                    class="inline-flex items-center self-start px-3 py-1 rounded-full bg-[#27AE60]/15 text-[#27AE60] font-label-md text-label-md uppercase tracking-wider">
                    Enviado a Aseguradora
                  </span>
                  <h2 class="font-headline-md text-headline-md text-on-surface">12 Oct 2023 - Calle 100, Bogotá</h2>
                  <p class="font-body-md text-on-surface-variant flex items-center gap-1">
                    <span class="material-symbols-outlined text-[18px]">location_on</span>
                    Localidad de Usaquén, Bogotá D.C.
                  </p>
                </div>
              </section>
              <!-- Bento Grid: Fast Actions & Info -->
              <section class="px-margin-mobile grid grid-cols-2 gap-4 mb-8">
                <div
                  class="col-span-2 bg-surface-container-lowest p-5 rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.06)] border border-surface-variant/50">
                  <div class="flex justify-between items-start mb-4">
                    <div>
                      <p class="font-label-md text-label-md text-on-surface-variant uppercase">Vehículo involucrado</p>
                      <p class="font-headline-sm text-headline-sm">Toyota Corolla</p>
                      <p class="font-body-sm text-on-surface-variant">Placa: XYZ-789</p>
                    </div>
                    <span class="material-symbols-outlined text-primary text-4xl">directions_car</span>
                  </div>
                  <div class="flex gap-2">
                    <span
                      class="px-2 py-1 rounded bg-surface-container-high font-label-md text-label-md text-on-surface">Gris
                      Metálico</span>
                    <span
                      class="px-2 py-1 rounded bg-surface-container-high font-label-md text-label-md text-on-surface">Modelo
                      2022</span>
                  </div>
                </div>
              </section>
              <!-- Horizontal Photo Gallery -->
              <section class="mb-8">
                <div class="px-margin-mobile mb-4 flex justify-between items-center">
                  <h3 class="font-headline-sm text-headline-sm">Evidencia Fotográfica</h3>
                  <span class="font-label-lg text-label-lg text-primary">Ver todas</span>
                </div>
                <!-- Category: Ángulos -->
                <div class="mb-6">
                  <p
                    class="px-margin-mobile font-label-md text-label-md text-on-surface-variant uppercase mb-3 px-margin-mobile">
                    Ángulos Generales</p>
                  <div class="flex overflow-x-auto gap-3 px-margin-mobile hide-scrollbar">
                    <div class="flex-none w-48 h-32 rounded-xl overflow-hidden shadow-sm relative group">
                      <img class="w-full h-full object-cover"
                        data-alt="A dramatic wide-angle photograph of a silver sedan with front bumper damage on a modern city street in Bogotá during daylight. The lighting is slightly overcast, casting soft shadows. The shot focuses on the car's orientation relative to the street lane. The background features clean urban architecture and blurred traffic signs, maintaining a professional documentary aesthetic with neutral tones and sharp details."
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuAv7OMSW_laVB3lPaKg8adOQeKbs-g6Ct016Hu4arsPTA2KpX8OOjlGNMZUp-rIRQbpPf1MCXPq1iSDaLRtNmA_dCbX2tLG2-Vi0BRHDhYF56IMTbDBSRjfAmub8jge8GWbP9Hq1V5nq365RSr6qTS0eaiCPCrgBpB4Rhxp-FJlY5dujuc24eVH6YmwDuAYePIZ0Yj40ZgvEIch0NjDxYxvtL188uR9oTpjrGC9iUeK3NoMhtQSdOC-4QnysW1uhBEd7ktu4WuwIvk" />
                      <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                        <p class="text-white font-label-md text-[10px]">Frontal Derecho</p>
                      </div>
                    </div>
                    <div class="flex-none w-48 h-32 rounded-xl overflow-hidden shadow-sm relative">
                      <img class="w-full h-full object-cover"
                        data-alt="A medium shot showing the rear side view of a silver Toyota Corolla on an urban asphalt road. The image captures the vehicle's position against the curb. The lighting is crisp afternoon sun, highlighting the metallic finish of the car. The composition is clean and technical, emphasizing the car's placement in the accident scene for insurance documentation purposes."
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuA9nGPa54ukE1Pe7ExMAmuwGSY2aECMUoNfOSY6JrbBPk11tiGLm-9g9SVroQ2ODyz1CKgEy0zOy1P4C2Q77bsAW74g01pCt_52y8B2QW8z6Ukzhx1X8cSalDOcC9crveo9VoyX9SZ9-O0ecaJhAynEyr_fbBjBT6y5OZ8-_WMt3uSdL_sLNvHzAl8LnI8F15LgNrCdyV6UkWSGF6ZzJKZ9ppSxC7antOkVgWfmC3A6abe91OcC-F_C4-ZpPpx_3NZmJApUA_OktTA" />
                      <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                        <p class="text-white font-label-md text-[10px]">Posterior Izquierdo</p>
                      </div>
                    </div>
                    <div
                      class="flex-none w-48 h-32 rounded-xl bg-surface-container flex flex-col items-center justify-center border-2 border-dashed border-outline-variant text-on-surface-variant">
                      <span class="material-symbols-outlined">add_a_photo</span>
                      <span class="font-label-md text-[10px] mt-1">Añadir</span>
                    </div>
                  </div>
                </div>
                <!-- Category: Terceros -->
                <div>
                  <p class="px-margin-mobile font-label-md text-label-md text-on-surface-variant uppercase mb-3">
                    Vehículos de Terceros</p>
                  <div class="flex overflow-x-auto gap-3 px-margin-mobile hide-scrollbar">
                    <div class="flex-none w-48 h-32 rounded-xl overflow-hidden shadow-sm relative">
                      <img class="w-full h-full object-cover"
                        data-alt="Technical close-up photograph of a black SUV's license plate and rear light assembly involved in a minor collision. The image is taken at eye-level with clear visibility of the plate numbers. The setting is a clean, modern city street with bright daytime lighting. The focus is sharp on the vehicle details, emphasizing clarity for legal and insurance identification in a minimalist corporate style."
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuABZg636uapXRi6txx0JB4VABYB7R4dWxuGVAgHHsUfCggzJjK3ArkiPvs8M-pq7cmVtthMEFfvDgWTNlDnHYfsaO7iPGN7BQfCoxrwzvVJ5AIlS0rGb5gpuH_jGHgiztuwsBvjIC5Yk9uAmtO-OPuGpn4Ptau7YioDDJi2n47SSUiGcpZQnWdq3btVk3G7XKddtZx8rc0-q0QQYvHIQ4LvUifXcKpNVJHshLvC2pKvWMzDM1OjB8UWnp_ftC07za4XcDa0u2nwfEQ" />
                      <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                        <p class="text-white font-label-md text-[10px]">Placa Tercero</p>
                      </div>
                    </div>
                    <div class="flex-none w-48 h-32 rounded-xl overflow-hidden shadow-sm relative">
                      <img class="w-full h-full object-cover"
                        data-alt="Insurance evidence photo capturing the side impact damage on a white hatchback vehicle. The shot is well-lit and clearly shows scratches and a dent on the passenger door. The environment is a gray asphalt road. The photographic style is clinical and objective, avoiding artistic filters to ensure the most accurate representation of the vehicle's condition for claim processing."
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuAFl65nM_6DslR9tR0X2eLyAHHRMqq9ZtBd60VMVx3qvjAyexSfAnGEA82UrUGwqj3XCVjnacSW5aH8NMpYoGmflb2TTF3XYxgeQZIlRELNC7PLXnyGtyZChy_eCJsjqeGkWkWvlVTJftvtV9Qz2_M78BaEI3eqS6OBnEFsD4c4dnWaUldxV9M73na3hyBosYyuH09JPjwNqQDCP0JMTgYRqZcVqGUvQ7P099dQRMnFeHyBarW_dcWPOUnOykUQaG9T7lx7PCh0eoQ" />
                      <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                        <p class="text-white font-label-md text-[10px]">Lateral Tercero</p>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
              <!-- Timeline Section -->
              <section class="px-margin-mobile mb-8">
                <h3 class="font-headline-sm text-headline-sm mb-6">Estado del Trámite</h3>
                <div
                  class="relative pl-8 space-y-8 before:content-[''] before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-[2px] before:bg-surface-container-highest">
                  <!-- Step 3 (Active) -->
                  <div class="relative">
                    <div
                      class="absolute -left-[30px] w-6 h-6 rounded-full bg-primary border-4 border-surface flex items-center justify-center z-10">
                      <span class="material-symbols-outlined text-white text-[12px]"
                        style="font-variation-settings: 'FILL' 1;">check</span>
                    </div>
                    <div>
                      <p class="font-label-lg text-label-lg text-primary">Notificado</p>
                      <p class="font-body-sm text-on-surface-variant">12 Oct 2023 - 10:45 AM</p>
                      <p class="font-body-md mt-1">La aseguradora ha recibido la evidencia y ha iniciado el caso
                        #ACC-9041.</p>
                    </div>
                  </div>
                  <!-- Step 2 -->
                  <div class="relative">
                    <div
                      class="absolute -left-[30px] w-6 h-6 rounded-full bg-surface-container-highest border-4 border-surface flex items-center justify-center z-10">
                      <span class="material-symbols-outlined text-on-surface-variant text-[12px]"
                        style="font-variation-settings: 'FILL' 1;">check</span>
                    </div>
                    <div>
                      <p class="font-label-lg text-label-lg text-on-surface">Fotos cargadas</p>
                      <p class="font-body-sm text-on-surface-variant">12 Oct 2023 - 10:30 AM</p>
                    </div>
                  </div>
                  <!-- Step 1 -->
                  <div class="relative">
                    <div
                      class="absolute -left-[30px] w-6 h-6 rounded-full bg-surface-container-highest border-4 border-surface flex items-center justify-center z-10">
                      <span class="material-symbols-outlined text-on-surface-variant text-[12px]"
                        style="font-variation-settings: 'FILL' 1;">check</span>
                    </div>
                    <div>
                      <p class="font-label-lg text-label-lg text-on-surface">Reportado</p>
                      <p class="font-body-sm text-on-surface-variant">12 Oct 2023 - 10:15 AM</p>
                    </div>
                  </div>
                </div>
              </section>
              <!-- CTA Action Buttons -->
              <section class="px-margin-mobile flex flex-col gap-4">
                <button
                  class="w-full h-tap-target-min bg-primary text-on-primary rounded-xl font-label-lg text-label-lg flex items-center justify-center gap-2 shadow-lg shadow-primary/20 active:scale-95 transition-transform">
                  <span class="material-symbols-outlined">description</span>
                  Generar reporte
                </button>
                <div class="grid grid-cols-2 gap-4">
                  <button
                    class="h-tap-target-min border border-outline text-on-surface rounded-xl font-label-lg text-label-lg flex items-center justify-center gap-2 hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined">share</span>
                    Compartir
                  </button>
                  <button
                    class="h-tap-target-min bg-secondary text-on-secondary rounded-xl font-label-lg text-label-lg flex items-center justify-center gap-2 hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined">support_agent</span>
                    Contactar
                  </button>
                </div>
              </section>
            </main>
          </body>

          </html>
        </div>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- ╔══════════════════════════════════════════╗ -->
  <!-- ║  CTA                                     ║ -->
  <!-- ╚══════════════════════════════════════════╝ -->
  <section class="cta-section" id="cta">
    <div class="container" style="position:relative;z-index:1;">
      <div class="badge badge-primary" style="margin-bottom:20px;display:inline-flex;">Disponible para Android</div>
      <h2 class="cta-title fade-in">Instala ChocApp.<br>Espera no tener que usarla.</h2>
      <p class="cta-body fade-in">La tranquilidad de saber qué hacer en una emergencia vale más que cualquier
        preparación teórica.</p>
      <div class="cta-actions fade-in">
        <a href="#" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
            <path
              d="M3.18 23.76c.28.15.6.2.91.14l12.82-11.9-3.01-3.01-10.72 14.77zM20.65 9.6L17.4 7.74l-3.39 3.15 3.39 3.14 3.27-1.88c.93-.54.93-2.01-.02-2.55zM.35.37A1.03 1.03 0 000 1.16v21.68c0 .32.13.61.35.79l.11.08L12.38 12 .46.29.35.37zM13.05 12l3.01-3.01-12.82-11.9c-.31-.06-.63-.01-.91.14L13.05 12z" />
          </svg>
          Descargar en Google Play
        </a>
        <a href="#problema" class="btn btn-outline">Ver la problemática</a>
      </div>
    </div>
  </section>

  <!-- ╔══════════════════════════════════════════╗ -->
  <!-- ║  FOOTER                                  ║ -->
  <!-- ╚══════════════════════════════════════════╝ -->
  <footer>
    <div class="footer-logo">
      <div class="footer-logo-dot"></div>
      ChocApp
    </div>
    <div class="footer-text">Solución de emergencia vial · Android · Colombia · 2026</div>
  </footer>

  <script>
    // Intersection Observer para fade-in
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.fade-in').forEach((el, i) => {
      // Aplica stagger si el padre tiene clase stagger
      if (el.parentElement && el.parentElement.classList.contains('stagger')) {
        el.style.transitionDelay = `${(parseInt(el.style.getPropertyValue('--i') || 0)) * 80}ms`;
      }
      observer.observe(el);
    });

    // Activar elementos ya visibles al cargar
    document.querySelectorAll('.fade-in').forEach(el => {
      const rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight) el.classList.add('visible');
    });
  </script>

</body>

</html>