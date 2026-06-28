{{-- Account tabs + forms — use dashboard type scale (--fs-body 16, --fs-step 18, --fs-title 20) --}}
<style>
.portfolio-tab {
  display: inline-block;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: var(--fs-body);
  font-weight: 600;
  text-decoration: none;
  color: var(--text-mid);
  transition: all 0.15s;
}
.portfolio-tab:hover { color: var(--text-dark); }
.portfolio-tab.active { background: var(--white); color: var(--text-dark); box-shadow: 0 1px 3px rgba(0,0,0,0.08); }

.rm-check-grid { display: flex; flex-direction: column; gap: 10px; margin: 16px 0; }
.rm-check-option {
  display: flex; align-items: flex-start; gap: 10px;
  padding: 12px 14px; border: 1px solid var(--cream-dark); border-radius: 10px;
  background: var(--white); cursor: pointer;
}
.rm-check-option:has(input:disabled) { opacity: 0.55; cursor: not-allowed; }
.rm-check-option:has(input:checked) { border-color: var(--terra); background: var(--terra-pale); }
.rm-check-option input { margin-top: 3px; flex-shrink: 0; accent-color: var(--terra); }
.rm-check-option strong { display: block; font-size: var(--fs-body); color: var(--text-dark); }
.rm-check-option span { font-size: var(--fs-step); color: var(--text-light); }

.rm-pm-section { margin-bottom: 28px; }
.rm-pm-section h3 {
  font-family: 'Fraunces', serif;
  font-size: var(--fs-title);
  font-weight: 600;
  margin: 0 0 12px;
  color: var(--text-dark);
}

.rm-detail-rows { display: flex; flex-direction: column; gap: 10px; }
.rm-detail-row {
  display: grid;
  grid-template-columns: minmax(140px, 38%) 1fr;
  gap: 12px 24px;
  align-items: baseline;
  font-size: var(--fs-body);
  padding: 8px 0;
  border-bottom: 1px solid var(--cream-dark);
}
.rm-detail-row:last-child { border-bottom: none; }
.rm-detail-label { color: var(--text-light); font-weight: 600; }
.rm-detail-value { text-align: left; color: var(--text-dark); }

.rm-acc-table { width: 100%; border-collapse: collapse; font-size: var(--fs-body); }
.rm-acc-table th {
  text-align: left; font-weight: 600; color: var(--text-light);
  padding: 10px 20px 10px 0; vertical-align: top; white-space: nowrap; width: 38%;
}
.rm-acc-table td {
  text-align: left; color: var(--text-dark);
  padding: 10px 0; vertical-align: top; border-bottom: 1px solid var(--cream-dark);
}
.rm-acc-table tr:last-child th, .rm-acc-table tr:last-child td { border-bottom: none; }

.rm-pm-tab-count {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 18px; height: 18px; padding: 0 5px; margin-left: 6px;
  font-size: var(--fs-body); font-weight: 700; border-radius: 9px;
  background: var(--cream-dark); color: var(--text-mid);
}
.portfolio-tab.active .rm-pm-tab-count { background: var(--terra-pale); color: var(--terra); }

.rm-doc-card { margin-bottom: 16px; }
.rm-doc-status {
  font-size: var(--fs-body); font-weight: 600; text-transform: uppercase;
  letter-spacing: 0.04em; margin-left: auto;
}
.rm-doc-status--verified { color: var(--green); }
.rm-doc-status--pending { color: var(--gold); }
.rm-doc-status--rejected { color: var(--red); }
.rm-doc-status--none { color: var(--text-light); }

.db-table-link { color: var(--terra); font-weight: 500; text-decoration: none; }
.db-table-link:hover { text-decoration: underline; }

.db-content--account .db-form-submit { background: var(--terra); }
.db-content--account .db-form-submit:hover { background: var(--terra-light); }
.db-content--account .db-input:focus,
.db-content--account .db-select:focus,
.db-content--account .db-textarea:focus { border-color: var(--terra); background: var(--white); }
.db-content--account .db-btn-primary { background: var(--terra); color: var(--white); }
.db-content--account .db-form { max-width: 600px; }
.db-content--account .db-form--wide { max-width: none; width: 100%; }
.db-content--account .db-form-group label .req { color: var(--terra); }

.rm-acc-section-spaced { margin-top: 18px; }
.rm-acc-page-lead {
  margin: 0 0 18px; max-width: 52rem;
  font-size: var(--fs-body); line-height: 1.55; color: var(--text-mid); font-weight: 400;
}
.rm-acc-status-label {
  margin: 0 0 4px;
  font-size: var(--fs-body); font-weight: 600;
  letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-light);
}
.rm-acc-status-value { margin: 0; font-size: var(--fs-step); color: var(--text-dark); }
.rm-acc-field-group-label {
  margin: 20px 0 10px; padding-top: 20px; border-top: 1px solid var(--cream-dark);
  font-size: var(--fs-body); font-weight: 600;
  letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-light);
}
.rm-card-billing { margin-top: 4px; }
.rm-card-billing .rm-acc-field-group-label { margin-top: 16px; padding-top: 16px; }
.rm-card-billing-fields { margin-top: 12px; }
.rm-acc-check-row { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 14px; cursor: pointer; font-weight: 400; }
.rm-acc-check-row input { margin-top: 3px; flex-shrink: 0; accent-color: var(--terra); }
.rm-acc-check-text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.rm-acc-check-title { font-size: var(--fs-step); font-weight: 500; color: var(--text-dark); line-height: 1.35; }
.rm-acc-check-meta { font-size: var(--fs-body); font-weight: 400; color: var(--text-light); line-height: 1.45; }

/* Portfolio activation */
.rm-activate-intro { margin: 0 0 20px; font-size: var(--fs-body); color: var(--text-mid); line-height: 1.55; }
.rm-activate-steps { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 16px; }
.rm-activate-step { border: 1px solid var(--cream-dark); border-radius: 10px; padding: 16px 18px; background: var(--white); }
.rm-activate-step--done { border-color: rgba(46,125,87,0.35); }
.rm-activate-step--current { border-color: var(--terra); box-shadow: 0 0 0 1px var(--terra-pale); }
.rm-activate-step--locked { opacity: 0.65; }
.rm-activate-step-head { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.rm-activate-step-num {
  display: inline-flex; align-items: center; justify-content: center;
  width: 26px; height: 26px; border-radius: 50%;
  font-size: var(--fs-body); font-weight: 600;
  background: var(--cream-dark); color: var(--text-mid); flex-shrink: 0;
}
.rm-activate-step--done .rm-activate-step-num { background: var(--green-pale); color: var(--green); }
.rm-activate-step--current .rm-activate-step-num { background: var(--terra); color: var(--white); }
.rm-activate-step-title { font-size: var(--fs-title); font-weight: 600; color: var(--text-dark); }
.rm-activate-step-meta { margin: 0; font-size: var(--fs-body); color: var(--text-mid); line-height: 1.5; }
.rm-activate-pricing { margin: 14px 0 16px; padding: 14px 16px; background: var(--cream); border-radius: 8px; }
.rm-activate-pricing-row {
  display: flex; justify-content: space-between; align-items: baseline;
  font-size: var(--fs-body); padding: 4px 0;
}
.rm-activate-pricing-row--total {
  margin-top: 6px; padding-top: 10px; border-top: 1px solid var(--cream-dark);
  font-size: var(--fs-step);
}
.rm-activate-pricing-row--total strong { font-size: var(--fs-title); }
</style>
