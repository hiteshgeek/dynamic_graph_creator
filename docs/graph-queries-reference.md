# Graph Queries Reference

Based on analysis of `DashboardElementData::populateGraphDataArray()` from the live project.

## Available Filters

| Filter ID | Placeholder | Label | Type | Purpose |
|-----------|-------------|-------|------|---------|
| 1 | `::company_list` | Companies | select/multi_select | Company selection |
| 2 | `::global_datepicker` | Global Datepicker | main_datepicker | Date range (`_from`, `_to`) |
| 3 | `::outlet_list` | Outlets | multi_select | Outlet filtering |

---

## PIE CHARTS (Distribution/Top N)

### 1. Top 5 Customers by Net Sales
```sql
SELECT
    SUM(payable_amount - total_tax_amount) as net_sales,
    name
FROM invoice
WHERE invsid = 1
    AND company_id IN(::company_list)
    AND DATE(date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND outlet_chkid IN(::outlet_list)
GROUP BY cuid
ORDER BY net_sales DESC
LIMIT 5
```

### 2. Top 5 Sales Persons by Net Sales
```sql
SELECT
    SUM(payable_amount - total_tax_amount) as net_sales,
    c_assigned_user_name as name
FROM invoice
WHERE invsid = 1
    AND company_id IN(::company_list)
    AND DATE(date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND outlet_chkid IN(::outlet_list)
GROUP BY assigned_uid
ORDER BY net_sales DESC
LIMIT 5
```

### 3. Top 5 Categories by Net Sales
```sql
SELECT
    SUM(ii.discounted_amount * ((100 - i.discount) / 100)) as net_sales,
    category as name
FROM invoice_item ii
JOIN invoice i ON (ii.invid = i.invid AND i.invsid = 1
    AND i.company_id IN(::company_list)
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND i.outlet_chkid IN(::outlet_list))
JOIN inventory_set_variations isv ON (isv.isvid = ii.isvid AND isv.catid > 0)
GROUP BY isv.catid
ORDER BY net_sales DESC
LIMIT 5
```

### 4. Top 5 Brands by Net Sales
```sql
SELECT
    SUM(ii.discounted_amount * ((100 - i.discount) / 100)) as net_sales,
    c_brand as name
FROM invoice_item ii
JOIN invoice i ON (ii.invid = i.invid AND i.invsid = 1
    AND i.company_id IN(::company_list)
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND i.outlet_chkid IN(::outlet_list))
JOIN inventory_set_variations isv ON (isv.isvid = ii.isvid AND isv.c_bid > 0)
GROUP BY isv.c_bid
ORDER BY net_sales DESC
LIMIT 5
```

### 5. Top 5 States by Sales
```sql
SELECT
    s.state as name,
    SUM(i.payable_amount) as net_sales
FROM invoice i
JOIN state s ON (s.stid = i.billing_stid
    AND i.invsid = 1
    AND i.company_id IN(::company_list)
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND i.outlet_chkid IN(::outlet_list))
GROUP BY s.stid
ORDER BY net_sales DESC
LIMIT 5
```

### 6. Top 5 Countries by Sales
```sql
SELECT
    c.name as name,
    SUM(i.payable_amount) as net_sales
FROM invoice i
JOIN state s ON (s.stid = i.billing_stid
    AND i.invsid = 1
    AND i.company_id IN(::company_list)
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND i.outlet_chkid IN(::outlet_list))
JOIN country c ON (c.ctid = s.ctid)
GROUP BY c.ctid
ORDER BY net_sales DESC
LIMIT 5
```

### 20. Top 8 Products by Units Sold
```sql
SELECT
    SUM(ii.quantity / COALESCE(m.conversion_rate, 1)) as units_sold,
    ii.name as name
FROM invoice_item ii
JOIN invoice i ON (ii.invid = i.invid
    AND i.invsid = 1
    AND i.company_id IN(::company_list)
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND i.outlet_chkid IN(::outlet_list))
LEFT JOIN measurement m ON (m.meaid = ii.meaid)
WHERE ii.isvid > 0
GROUP BY ii.isvid
ORDER BY units_sold DESC
LIMIT 8
```

### 21. Top 8 Categories by Units Sold
```sql
SELECT
    SUM(ii.quantity / COALESCE(m.conversion_rate, 1)) as units_sold,
    isv.category as name
FROM invoice_item ii
JOIN invoice i ON (ii.invid = i.invid
    AND i.invsid = 1
    AND i.company_id IN(::company_list)
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND i.outlet_chkid IN(::outlet_list))
JOIN inventory_set_variations isv ON (isv.isvid = ii.isvid AND isv.catid > 0)
LEFT JOIN measurement m ON (m.meaid = ii.meaid)
WHERE ii.isvid > 0
GROUP BY isv.catid
ORDER BY units_sold DESC
LIMIT 8
```

### 25. Leads by Status
```sql
SELECT
    COUNT(*) as count,
    ls.name
FROM lead l
JOIN lead_status ls ON (ls.leasid = l.leasid)
WHERE l.company_id IN(::company_list)
    AND DATE(date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
GROUP BY l.leasid
```

### 26. Quotations by Status
```sql
SELECT
    COUNT(*) as count,
    qs.name
FROM quotation q
JOIN quotation_status qs ON (qs.qosid = q.qosid)
WHERE q.company_id IN(::company_list)
    AND DATE(date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
GROUP BY q.qosid
```

### 27. Orders by Status
```sql
SELECT
    COUNT(*) as count,
    os.name
FROM checkpoint_order c
JOIN checkpoint_order_status os ON (os.chkosid = c.chkosid)
WHERE c.company_id IN(::company_list)
    AND DATE(taken_date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
GROUP BY c.chkosid
```

### 28. Top 5 Products by Sales Value
```sql
SELECT
    SUM(ii.discounted_amount * ((100 - i.discount) / 100)) as net_sales,
    ii.name as name
FROM invoice_item ii
JOIN invoice i ON (ii.invid = i.invid
    AND i.invsid = 1
    AND i.company_id IN(::company_list)
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND i.outlet_chkid IN(::outlet_list))
LEFT JOIN measurement m ON (m.meaid = ii.meaid)
WHERE ii.isvid > 0
GROUP BY ii.isvid
ORDER BY net_sales DESC
LIMIT 5
```

### 30. Sales by Customer Type
```sql
SELECT
    SUM(payable_amount) as net_sales,
    ct.name as name
FROM invoice i
JOIN customer c ON (c.cuid = i.cuid)
JOIN customer_type ct ON (ct.cutid = c.cutid)
WHERE i.invsid = 1
    AND i.company_id IN(::company_list)
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND i.outlet_chkid IN(::outlet_list)
GROUP BY ct.cutid
ORDER BY net_sales DESC
```

### 31. Collections by Payment Mode
```sql
SELECT
    COUNT(*) as amount,
    cpo.name as name
FROM transaction_reference t
JOIN account_voucher av ON (av.avid = t.avid)
JOIN customer_payment_option cpo ON (cpo.cpoid = av.cpoid)
WHERE t.avtid = 2
    AND t.tresid IN(1, 2)
    AND t.company_id IN(::company_list)
    AND DATE(t.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
GROUP BY av.cpoid
```

---

## BAR/LINE CHARTS (Monthly Trends)

### 7. Monthly Sales, Cost & Profit
```sql
SELECT
    MONTHNAME(inv.date) AS month,
    SUM(ii.discounted_amount - (ii.discounted_amount * (inv.discount / 100))) AS sales,
    SUM(iinv.quantity * invset.price) / SUM(ii.quantity) AS cost,
    (SUM(ii.discounted_amount - (ii.discounted_amount * (inv.discount / 100)))
     - SUM(iinv.quantity * invset.price) / SUM(ii.quantity)) AS profit
FROM invoice inv
JOIN invoice_item ii ON inv.invid = ii.invid
LEFT JOIN invoice_item_inventory iinv ON ii.inviid = iinv.inviid
LEFT JOIN inventory_set invset ON iinv.isid = invset.isid
WHERE inv.invsid = 1
    AND inv.company_id IN(::company_list)
    AND DATE(inv.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND inv.outlet_chkid IN(::outlet_list)
GROUP BY MONTH(inv.date)
ORDER BY MONTH(inv.date) ASC
```

### 8. Monthly Net Sales vs Collection
**Query 1 - Sales:**
```sql
SELECT
    SUM(i.payable_amount) as net_sales,
    MONTHNAME(i.date) as name
FROM invoice i
WHERE i.invsid = 1
    AND i.company_id IN(::company_list)
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND i.outlet_chkid IN(::outlet_list)
GROUP BY MONTH(i.date) ASC
```

**Query 2 - Collection:**
```sql
SELECT
    SUM(i.amount) as collection_amount,
    MONTHNAME(i.date) as name
FROM transaction_reference i
WHERE i.company_id IN(::company_list)
    AND avtid = 2
    AND i.tresid = 1
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
GROUP BY MONTH(i.date) ASC
```

### 9. Monthly Net Sales vs Net Purchase
**Query 1 - Sales:**
```sql
SELECT
    SUM(i.payable_amount - i.total_tax_amount) as net_sales,
    MONTHNAME(i.date) as name
FROM invoice i
WHERE i.invsid = 1
    AND i.company_id IN(::company_list)
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND i.outlet_chkid IN(::outlet_list)
GROUP BY MONTH(i.date) ASC
```

**Query 2 - Purchase:**
```sql
SELECT
    SUM(i.payable_amount - i.total_tax_amount) as net_purchase,
    MONTHNAME(i.date) as name
FROM purchase_invoice i
WHERE i.pinvsid = 1
    AND i.company_id IN(::company_list)
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
GROUP BY MONTH(i.date) ASC
```

### 10. Monthly Net Sales
```sql
SELECT
    SUM(payable_amount - total_tax_amount) as net_sales,
    MONTHNAME(date) as name
FROM invoice
WHERE invsid = 1
    AND company_id IN(::company_list)
    AND DATE(date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND outlet_chkid IN(::outlet_list)
GROUP BY MONTH(date)
ORDER BY MONTH(date) ASC
```

### 11. Monthly Commission
```sql
SELECT
    MONTHNAME(CASE WHEN i.invid > 0 THEN i.date ELSE c.return_date END) as name,
    SUM(CASE
        WHEN i.invid > 0 THEN cm.quotation_payable_amount * cm.value / 100
        ELSE 0 - cm.quotation_payable_amount * cm.value / 100
    END) as commission
FROM commission_mapping cm
LEFT JOIN invoice i ON (i.invid = cm.invid
    AND i.invsid = 1
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to)
LEFT JOIN checkpoint_order_credit_note c ON (c.chkocrnoteid = cm.chkocrnoteid
    AND c.chkocrnotesid = 2
    AND DATE(c.return_date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to)
WHERE (cm.invid > 0 OR cm.chkocrnoteid > 0)
    AND cm.company_id IN(::company_list)
GROUP BY MONTH(CASE WHEN i.invid > 0 THEN i.date ELSE c.return_date END)
ORDER BY MONTH(CASE WHEN i.invid > 0 THEN i.date ELSE c.return_date END) ASC
```

### 14. Monthly Lead Count
```sql
SELECT
    COUNT(*) as count,
    MONTHNAME(date) as name
FROM lead
WHERE company_id IN(::company_list)
    AND DATE(date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
GROUP BY MONTH(date) ASC
```

### 15. Monthly New Quotations
```sql
SELECT
    COUNT(*) as count,
    MONTHNAME(date) as name
FROM quotation
WHERE company_id IN(::company_list)
    AND DATE(date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
GROUP BY MONTH(date) ASC
```

### 16. Monthly New Customers
```sql
SELECT
    COUNT(*) as count,
    MONTHNAME(created_ts) as name
FROM customer
WHERE company_id IN(::company_list)
    AND DATE(created_ts) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
GROUP BY MONTH(created_ts) ASC
```

### 17. Monthly New Products
```sql
SELECT
    COUNT(*) as count,
    MONTHNAME(created_ts) as name
FROM inventory_set_variations
WHERE company_id IN(::company_list)
    AND DATE(created_ts) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
GROUP BY MONTH(created_ts) ASC
```

### 18. Monthly Collection
```sql
SELECT
    SUM(amount) as amount,
    MONTHNAME(date) as name
FROM transaction_reference
WHERE tresid IN(1, 2)
    AND avtid = 2
    AND company_id IN(::company_list)
    AND DATE(date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
GROUP BY MONTH(date) ASC
```

---

## GEO/MAP CHARTS

### 24. Sales by All States (GeoChart)
```sql
SELECT
    s.state as name,
    SUM(i.payable_amount) as net_sales
FROM invoice i
JOIN state s ON (s.stid = i.billing_stid
    AND i.invsid = 1
    AND i.company_id IN(::company_list)
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND i.outlet_chkid IN(::outlet_list))
GROUP BY s.stid
```

### 32. Sales by All Countries (GeoChart)
```sql
SELECT
    c.name as name,
    SUM(i.payable_amount) as net_sales
FROM invoice i
JOIN state s ON (s.stid = i.billing_stid
    AND i.invsid = 1
    AND i.company_id IN(::company_list)
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND i.outlet_chkid IN(::outlet_list))
JOIN country c ON (c.ctid = s.ctid)
GROUP BY c.ctid
ORDER BY net_sales
```

---

## COMPARISON CHARTS

### 22. Year-over-Year Outlet Sales Comparison
```sql
SELECT
    o.name,
    SUM(CASE
        WHEN DATE(i.date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
        THEN payable_amount - total_tax_amount
        ELSE 0
    END) as current_year,
    SUM(CASE
        WHEN DATE(i.date) BETWEEN 'next_year_start' AND 'next_year_end'
        THEN payable_amount - total_tax_amount
        ELSE 0
    END) as next_year
FROM invoice i
JOIN outlet o ON (o.chkid = i.outlet_chkid
    AND i.invsid = 1
    AND DATE(i.date) BETWEEN ::global_datepicker_from AND 'next_year_end')
WHERE i.company_id IN(::company_list)
GROUP BY i.outlet_chkid
```

### 29. Quarterly Accounts Payable vs Receivable
```sql
SELECT
    (CASE
        WHEN al.agid IN(creditor_groups) AND att.atrtid = 1 THEN amount
        WHEN al.agid IN(creditor_groups) AND att.atrtid = 1 THEN 0 - amount
    END) as payable,
    (CASE
        WHEN al.agid IN(debtor_groups) AND att.atrtid = 1 THEN amount
        WHEN al.agid IN(debtor_groups) AND att.atrtid = 1 THEN 0 - amount
    END) as receivable,
    av.date
FROM account_transaction att
JOIN account_voucher av ON (av.avid = att.avid
    AND av.avsid = 1
    AND DATE(date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
    AND av.company_id IN(::company_list))
JOIN account_ledger al ON (al.alid = att.alid AND al.agid IN(agid_list))
```
*Note: Results are aggregated into Q1, Q2, Q3, Q4 in PHP code*

---

## FUNNEL CHART

### 63. Lead-Enquiry-Quotation-Order Funnel
Combines 4 separate queries:

**Lead Count:**
```sql
SELECT COUNT(*) as count
FROM lead
WHERE company_id IN(::company_list)
    AND DATE(date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
```

**Enquiry Count:**
```sql
SELECT COUNT(*) as count
FROM enquiry
WHERE company_id IN(::company_list)
    AND DATE(taken_date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
```

**Quotation Count:**
```sql
SELECT COUNT(*) as count
FROM quotation
WHERE company_id IN(::company_list)
    AND DATE(date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
```

**Order Count:**
```sql
SELECT COUNT(*) as count
FROM checkpoint_order
WHERE company_id IN(::company_list)
    AND DATE(taken_date) BETWEEN ::global_datepicker_from AND ::global_datepicker_to
```

---

## Filter Requirements Summary

| Filter | Required By | Notes |
|--------|-------------|-------|
| `::company_list` | **All graphs** | Always required for multi-tenant data isolation |
| `::global_datepicker_from` | **All graphs** | Start date for date range |
| `::global_datepicker_to` | **All graphs** | End date for date range |
| `::outlet_list` | Invoice-based graphs | Outlet filtering for sales data |

**Graphs NOT requiring `::outlet_list`**: 14, 15, 16, 17, 18, 25, 26, 27, 29, 31, 63 (Lead, Quotation, Customer, Product, Collection, Status-based graphs)
