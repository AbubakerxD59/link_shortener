<style>
    .branded-domains-page {
        padding: 2rem 0 4rem;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-size: clamp(1.75rem, 4vw, 2.25rem);
        margin: 0 0 0.5rem;
    }

    .page-header p {
        margin: 0;
        color: var(--muted);
        font-size: 1rem;
        max-width: 640px;
        line-height: 1.6;
    }

    .panel-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
        margin-bottom: 2rem;
    }

    .panel-card h2 {
        font-size: 1.125rem;
        margin: 0 0 1.25rem;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--muted);
        text-decoration: none;
    }

    .back-link:hover {
        color: var(--accent);
    }

    .domain-panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .domain-lead {
        margin: 0;
        color: var(--muted);
        font-size: 0.9375rem;
        line-height: 1.6;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.625rem;
        border-radius: var(--radius-pill);
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-verified {
        background: var(--success-soft);
        color: var(--success);
    }

    .badge-pending {
        background: #fff7ed;
        color: #c2410c;
    }

    .badge-default {
        background: var(--accent-soft);
        color: var(--accent);
    }

    .domain-alert {
        padding: 0.75rem 0.875rem;
        border-radius: var(--radius);
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    .domain-alert-success {
        background: var(--success-soft);
        border: 1px solid rgba(5, 150, 105, 0.15);
        color: #047857;
    }

    .domain-alert-error {
        background: #fef2f2;
        border: 1px solid rgba(220, 38, 38, 0.15);
        color: var(--danger);
    }

    .domain-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--ink-soft);
        margin-bottom: 0.375rem;
    }

    .domain-input-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.625rem;
    }

    .domain-input {
        flex: 1;
        min-width: 220px;
        padding: 0.8125rem 0.875rem;
        font-family: var(--font-body);
        font-size: 1rem;
        border: 1px solid var(--line-strong);
        border-radius: var(--radius);
        background: var(--card);
        color: var(--ink);
    }

    .domain-input:focus {
        outline: none;
        border-color: var(--accent-light);
        box-shadow: 0 0 0 4px rgba(109, 40, 217, 0.1);
    }

    .domain-hint {
        margin: 0.5rem 0 0;
        font-size: 0.8125rem;
        color: var(--muted);
    }

    .domain-hint code,
    .domain-note code,
    .dns-value,
    .domains-table code {
        font-family: ui-monospace, 'Cascadia Code', monospace;
        font-size: 0.8125rem;
    }

    .domains-table-wrap {
        overflow-x: auto;
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        background: var(--card);
    }

    .domains-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
        min-width: 720px;
    }

    .domains-table th,
    .domains-table td {
        padding: 0.875rem 1rem;
        text-align: left;
        border-bottom: 1px solid var(--line);
        vertical-align: middle;
    }

    .domains-table th {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--muted);
        background: var(--surface);
    }

    .domains-table tbody tr:last-child td {
        border-bottom: none;
    }

    .domains-table tbody tr:hover {
        background: var(--accent-soft);
    }

    .domain-actions-cell {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .btn-sm {
        padding: 0.5rem 0.875rem;
        font-size: 0.8125rem;
    }

    .domain-setup {
        margin-top: 0;
    }

    .setup-intro h2 {
        font-size: 1.25rem;
        margin: 0 0 0.75rem;
    }

    .domain-setup-lead {
        margin: 0 0 0.75rem;
        color: var(--ink-soft);
        font-size: 1rem;
        line-height: 1.7;
    }

    .setup-time-note {
        margin: 0 0 1.5rem;
        padding: 0.75rem 1rem;
        background: #fffbeb;
        border: 1px solid rgba(245, 158, 11, 0.2);
        border-radius: var(--radius);
        color: #92400e;
        font-size: 0.9375rem;
        line-height: 1.55;
    }

    .plain-steps {
        display: grid;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .plain-step {
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        overflow: hidden;
        background: var(--card);
    }

    .plain-step-head {
        display: flex;
        gap: 0.875rem;
        align-items: flex-start;
        padding: 1rem 1.125rem;
        background: var(--surface);
    }

    .setup-step-num {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--accent);
        color: #fff;
        font-size: 0.9375rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .plain-step-head h3 {
        font-size: 1.0625rem;
        margin: 0 0 0.375rem;
        line-height: 1.3;
    }

    .plain-step-head p {
        margin: 0;
        font-size: 0.9375rem;
        color: var(--muted);
        line-height: 1.6;
    }

    .record-card {
        padding: 1rem 1.125rem 1.125rem;
        border-top: 1px solid var(--line);
    }

    .record-card-title {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin-bottom: 1rem;
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--ink-soft);
    }

    .dns-type {
        display: inline-flex;
        padding: 0.25rem 0.625rem;
        border-radius: 6px;
        background: var(--accent-soft);
        color: var(--accent);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    .record-fields {
        margin: 0;
        display: grid;
        gap: 0.875rem;
    }

    .record-field {
        display: grid;
        gap: 0.375rem;
    }

    .record-field dt {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .record-field dd {
        margin: 0;
        font-size: 0.9375rem;
        color: var(--ink);
    }

    .record-value-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }

    .dns-value {
        font-family: ui-monospace, 'Cascadia Code', monospace;
        font-size: 0.875rem;
        word-break: break-all;
        padding: 0.5rem 0.625rem;
        background: var(--surface);
        border-radius: 8px;
        border: 1px solid var(--line);
    }

    .record-tip {
        margin: 1rem 0 0;
        padding: 0.75rem 0.875rem;
        background: var(--surface);
        border-radius: var(--radius);
        font-size: 0.8125rem;
        color: var(--muted);
        line-height: 1.55;
    }

    .copy-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0;
        border: 1px solid var(--line-strong);
        border-radius: 8px;
        background: var(--card);
        color: var(--muted);
        cursor: pointer;
        transition: color 0.2s, background 0.2s, border-color 0.2s;
    }

    .copy-btn:hover {
        color: var(--accent);
        border-color: var(--accent-light);
        background: var(--accent-soft);
    }

    .copy-btn svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    .domain-note {
        padding: 1rem 1.125rem;
        background: var(--surface);
        border-radius: var(--radius-lg);
        margin-bottom: 1rem;
        font-size: 0.9375rem;
        line-height: 1.6;
    }

    .domain-note-success {
        background: var(--success-soft);
        border: 1px solid rgba(5, 150, 105, 0.15);
    }

    .domain-note strong {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--ink);
    }

    .domain-note p {
        margin: 0.625rem 0 0;
        color: var(--muted);
    }

    .domain-note code {
        display: block;
        margin-top: 0.5rem;
        word-break: break-all;
        font-family: ui-monospace, 'Cascadia Code', monospace;
        font-size: 0.9375rem;
        padding: 0.625rem 0.75rem;
        background: var(--card);
        border-radius: 8px;
        border: 1px solid rgba(5, 150, 105, 0.15);
    }

    .help-details {
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        background: var(--surface);
        overflow: hidden;
    }

    .help-details summary {
        padding: 0.875rem 1.125rem;
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--ink-soft);
        cursor: pointer;
        list-style: none;
    }

    .help-details summary::-webkit-details-marker {
        display: none;
    }

    .help-details summary::after {
        content: '+';
        float: right;
        color: var(--muted);
        font-weight: 400;
    }

    .help-details[open] summary::after {
        content: '−';
    }

    .help-details-body {
        padding: 0 1.125rem 1.125rem;
        border-top: 1px solid var(--line);
    }

    .faq-item {
        padding: 0.875rem 0;
        border-bottom: 1px solid var(--line);
    }

    .faq-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .faq-item strong {
        display: block;
        font-size: 0.9375rem;
        margin-bottom: 0.375rem;
        color: var(--ink);
    }

    .faq-item p {
        margin: 0;
        font-size: 0.875rem;
        color: var(--muted);
        line-height: 1.6;
    }

    .domain-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.625rem;
        margin-top: 1.5rem;
    }

    .domain-remove-btn {
        color: var(--danger);
        border-color: rgba(220, 38, 38, 0.2);
    }

    .domain-remove-btn:hover:not(:disabled) {
        background: #fef2f2;
        border-color: rgba(220, 38, 38, 0.35);
    }

    .empty-state {
        text-align: center;
        padding: 2.5rem 1.5rem;
        color: var(--muted);
    }

    .empty-state h3 {
        font-size: 1.0625rem;
        color: var(--ink-soft);
        margin: 0 0 0.5rem;
    }

    .empty-state p {
        margin: 0;
        font-size: 0.9375rem;
    }

    .date-muted {
        font-size: 0.8125rem;
        color: var(--muted);
        white-space: nowrap;
    }
</style>
