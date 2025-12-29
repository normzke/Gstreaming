# BingeTV Pricing Implementation Plan

## Your Pricing Requirements:

### Monthly Package (30 days):
- 1 device: KSh 2,500/month
- 2 devices: KSh 2,500 + KSh 2,000 = KSh 4,500/month
- 3 devices: KSh 2,500 + KSh 2,000 + KSh 2,000 = KSh 6,500/month
- **Maximum: 3 devices** (advise to buy another package if more needed)

**Formula:** `Base Price + (Extra Devices × 2000)`
- Extra devices get KSh 500 discount (KSh 2,500 - KSh 500 = KSh 2,000)

### 6-Month Package (180 days):
- 1 device: KSh 2,000/month × 6 = KSh 12,000 total (KSh 500 discount per month)
- 2 devices: KSh 2,000 + KSh 2,000 = KSh 4,000/month × 6 = KSh 24,000 total
- 3 devices: KSh 2,000 + KSh 2,000 + KSh 2,000 = KSh 6,000/month × 6 = KSh 36,000 total
- **Maximum: 3 devices**

**Formula:** `(Base Discounted Price + (Extra Devices × 2000)) × 6`
- Base price: KSh 2,000/month (KSh 500 discount from monthly)
- Extra devices: KSh 2,000 each

### 1-Year Package (365 days):
- 1 device: KSh 1,800/month × 12 = KSh 21,600 total
- 2-3 devices: **Contact for custom package**
- **Maximum: 1 device** (more devices require custom quote)

**Formula:** `1800 × 12 = 21,600`
- Only 1 device allowed
- Show "Contact us for multi-device annual plans"

---

## Current Implementation Analysis:

### ✅ What's Already Working:
1. **Database Schema:**
   - `packages.price` - Base price per month
   - `packages.duration_days` - Package duration (30, 180, 365)
   - `packages.max_devices` - Devices included
   
2. **Admin Panel:**
   - Can create/edit packages
   - Can set price, duration, max_devices
   - Fields already exist

3. **Current Pricing Logic** (user/subscriptions/subscribe.php lines 36-40):
   ```php
   $minDevices = (int)($package['max_devices'] ?? 1);
   $extraDevices = max(0, $selectedDevices - $minDevices);
   $perMonth = (float)$package['price'] + ($extraDevices * 500);
   $totalPrice = $perMonth * max(1, $selectedMonths);
   ```

### ❌ What Needs to Change:

#### Issue 1: Extra Device Price
**Current:** `$extraDevices * 500` (adds KSh 500 per device)
**Should be:** `$extraDevices * 2000` (discounted price)

#### Issue 2: No 3-Device Limit
**Current:** No limit
**Should be:** Maximum 3 devices, show message if user tries more

#### Issue 3: Duration-Based Pricing
**Current:** Uses `$selectedMonths` (user selects duration)
**Should be:** Use package's `duration_days` (1, 6, or 12 months pre-defined)

#### Issue 4: Discount Logic
**Current:** No discounts for longer durations
**Should be:** 
- Monthly: Full price (2500)
- 6-month: Discounted (2000/month)
- Yearly: More discounted (1800/month)

#### Issue 5: Yearly Multi-Device Restriction
**Current:** Allows any devices
**Should be:** Only 1 device for yearly, show "custom package" for more

---

## 🔧 Required Changes:

### 1. Database - Add Pricing Fields

Add to `packages` table:
```sql
ALTER TABLE packages ADD COLUMN IF NOT EXISTS extra_device_price DECIMAL(10,2) DEFAULT 2000;
ALTER TABLE packages ADD COLUMN IF NOT EXISTS max_devices_limit INTEGER DEFAULT 3;
ALTER TABLE packages ADD COLUMN IF NOT EXISTS allow_multi_device BOOLEAN DEFAULT true;
```

**Or** use existing fields smartly without migration:
- Use package `features` JSON to store extra pricing rules
- Keep it simple with calculation logic

### 2. Update subscribe.php Pricing Logic

**Current (lines 36-40):**
```php
$minDevices = (int)($package['max_devices'] ?? 1);
$extraDevices = max(0, $selectedDevices - $minDevices);
$perMonth = (float)$package['price'] + ($extraDevices * 500);
$totalPrice = $perMonth * max(1, $selectedMonths);
```

**New Logic:**
```php
// Get package duration in months
$packageMonths = round($package['duration_days'] / 30);

// Device limits based on duration
$maxAllowedDevices = 3;
if ($packageMonths >= 12) {
    $maxAllowedDevices = 1; // Yearly: 1 device only
}

// Enforce device limit
if ($selectedDevices > $maxAllowedDevices) {
    $error = "This package allows maximum $maxAllowedDevices device(s). Please contact us for a custom package.";
    $selectedDevices = $maxAllowedDevices;
}

// Calculate pricing based on duration
$baseMonthlyPrice = (float)$package['price']; // Price per month for 1 device
$extraDevices = max(0, $selectedDevices - 1); // Extra devices beyond first

// Monthly rate per device based on package duration
if ($packageMonths >= 12) {
    // Yearly: 1800/month for 1 device only
    $monthlyRate = 1800;
    $extraDeviceRate = 0; // Not allowed for yearly
} elseif ($packageMonths >= 6) {
    // 6-month: 2000/month per device
    $monthlyRate = 2000;
    $extraDeviceRate = 2000;
} else {
    // Monthly: 2500 for first device, 2000 for extra
    $monthlyRate = 2500;
    $extraDeviceRate = 2000;
}

// Calculate total
$totalMonthlyPrice = $monthlyRate + ($extraDevices * $extraDeviceRate);
$totalPrice = $totalMonthlyPrice * $packageMonths;
```

### 3. Update Admin Panel

In `admin/packages.php`, when adding/editing packages, set:

**For Monthly Package (30 days):**
```
Name: Monthly Plan
Price: 2500 (per month, 1 device)
Duration: 30 days
Max Devices: 3
```

**For 6-Month Package (180 days):**
```
Name: 6-Month Plan  
Price: 2000 (per month, 1 device with discount)
Duration: 180 days
Max Devices: 3
```

**For Yearly Package (365 days):**
```
Name: Annual Plan
Price: 1800 (per month, 1 device with bigger discount)
Duration: 365 days
Max Devices: 1 (enforced in code)
```

### 4. Update Frontend JavaScript

In `public/js/enhanced.js` line 125:
```javascript
// OLD:
const perMonth = basePrice + (extraDevices * 500);

// NEW:
const packageMonths = parseInt(card.getAttribute('data-duration') || '1', 10);
let monthlyRate = basePrice;
let extraDeviceRate = 2000;

// Adjust rates based on duration
if (packageMonths >= 12) {
    monthlyRate = 1800;
    extraDeviceRate = 0; // Yearly: 1 device only
} else if (packageMonths >= 6) {
    monthlyRate = 2000;
    extraDeviceRate = 2000;
}

const perMonth = monthlyRate + (extraDevices * extraDeviceRate);
```

---

## ✅ Can Current System Handle This?

**YES!** With minor modifications:

### What's Already There:
- ✅ Database has `price`, `duration_days`, `max_devices`
- ✅ Admin panel can set all these fields
- ✅ Subscription page calculates pricing
- ✅ Frontend JavaScript updates prices dynamically

### What Needs to Change:
- Update pricing calculation logic (2 files)
- Add 3-device limit validation
- Add yearly package restrictions
- Update admin instructions

### No Database Changes Needed!
You can use the existing schema:
- Store base monthly price in `price` field
- Use `duration_days` to determine discount tier
- Use `max_devices` as a limit (set to 3 for most, 1 for yearly)

---

## 🎯 Recommended Approach:

### Option 1: Simple (Use Existing Fields)
- Set prices in admin based on duration
- Monthly: price = 2500
- 6-month: price = 2000  
- Yearly: price = 1800
- Update calculation logic to use package duration

### Option 2: Flexible (Add Helper Function)
- Create `calculatePackagePrice($package, $devices)` function
- Encapsulate all pricing logic
- Easy to update rules later

### Option 3: Advanced (Add Pricing Table)
- Create `package_pricing_rules` table
- Store tier-based pricing
- Most flexible for future changes

---

## 💡 My Recommendation:

**Use Option 1 (Simple)** - It works with your current database and is easy to manage:

1. In admin panel, create 3 packages:
   - Monthly (30 days, price 2500, max 3 devices)
   - 6-Month (180 days, price 2000, max 3 devices)
   - Annual (365 days, price 1800, max 1 device)

2. Update 2 files with new pricing logic

3. Test and deploy

**Time to implement: ~30 minutes**

Would you like me to implement this now?

