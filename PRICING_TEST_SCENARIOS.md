# BingeTV Pricing Test Scenarios

## New Pricing Model Implemented ✅

### Monthly Package (30 days):
| Devices | Calculation | Monthly Cost | Notes |
|---------|-------------|--------------|-------|
| 1 device | 2500 | KSh 2,500/month | Base price |
| 2 devices | 2500 + 2000 | KSh 4,500/month | 500 discount per extra device |
| 3 devices | 2500 + 2000 + 2000 | KSh 6,500/month | Max 3 devices |
| 4+ devices | BLOCKED | - | Show message: "Max 3 devices" |

### 6-Month Package (180 days):
| Devices | Calculation | Total Cost | Monthly Rate | Notes |
|---------|-------------|------------|--------------|-------|
| 1 device | 2000 × 6 | KSh 12,000 | 2000/month | 500 discount vs monthly |
| 2 devices | (2000 + 2000) × 6 | KSh 24,000 | 4000/month | Both devices at 2000 |
| 3 devices | (2000 + 2000 + 2000) × 6 | KSh 36,000 | 6000/month | All devices at 2000 |
| 4+ devices | BLOCKED | - | - | Max 3 devices |

### Yearly Package (365 days ≈ 12 months):
| Devices | Calculation | Total Cost | Monthly Rate | Notes |
|---------|-------------|------------|--------------|-------|
| 1 device | 1800 × 12 | KSh 21,600 | 1800/month | Best rate |
| 2+ devices | BLOCKED | - | - | Contact for custom package |

## Admin Panel Setup Required:

To make this work, create packages in admin with:

### Package 1: Monthly Plan
```
Name: Monthly Plan
Price: 2500 (will be overridden by code logic)
Duration: 30 days
Max Devices: 3
```

### Package 2: 6-Month Plan
```
Name: 6-Month Plan
Price: 2000 (will be overridden by code logic)
Duration: 180 days
Max Devices: 3
```

### Package 3: Annual Plan
```
Name: Annual Plan
Price: 1800 (will be overridden by code logic)
Duration: 365 days
Max Devices: 1
```

**Note:** The pricing logic in the code will calculate based on duration_days, not the price field directly!

## Code Changes Made:

### 1. Backend (user/subscriptions/subscribe.php):
- ✅ Added device limit enforcement (3 for monthly/6-month, 1 for yearly)
- ✅ Implemented duration-based pricing (2500/2000/1800)
- ✅ Extra device pricing at 2000 (500 discount from base 2500)
- ✅ Warning message for device limits

### 2. Frontend (public/js/enhanced.js):
- ✅ Updated pricing calculator to match backend logic
- ✅ Added device limit checks
- ✅ Duration-based rate calculations
- ✅ Real-time price updates as user selects devices

## Testing Checklist:

### Test Scenario 1: Monthly Package with 2 Devices
- Select Monthly package
- Choose 2 devices
- Expected: KSh 4,500/month (2500 + 2000)
- Duration: 1 month
- Total: KSh 4,500

### Test Scenario 2: 6-Month Package with 3 Devices
- Select 6-Month package
- Choose 3 devices
- Expected: KSh 6,000/month (2000 + 2000 + 2000)
- Duration: 6 months
- Total: KSh 36,000

### Test Scenario 3: Yearly Package (Should Block Multiple Devices)
- Select Annual package
- Try to choose 2+ devices
- Expected: Limited to 1 device
- Show message: "Annual packages support 1 device only"
- Total: KSh 21,600 (1800 × 12)

### Test Scenario 4: Try 4+ Devices (Should Block)
- Select any monthly/6-month package
- Try to choose 4 devices
- Expected: Limited to 3 devices
- Show message: "Maximum 3 devices per package"

## Implementation Status:

- ✅ Backend pricing logic updated
- ✅ Frontend JavaScript updated
- ✅ Device limits enforced
- ✅ Warning messages implemented
- ⏳ Needs to be synced to remote
- ⏳ Needs testing on live site

## Next Steps:

1. Sync pricing changes to remote server
2. Test on live site with all scenarios
3. Verify calculations are correct
4. Test edge cases (0 devices, negative numbers, etc.)

