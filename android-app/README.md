# Haat Bazar Android App

A WebView-based Android application wrapper for the Family Haat Bazar e-commerce website.

## Features

- **WebView Integration**: Seamlessly loads the Haat Bazar website
- **Splash Screen**: Professional app launch experience
- **Pull-to-Refresh**: Easy content refresh functionality
- **Network Detection**: Handles offline scenarios gracefully
- **Back Navigation**: Proper web navigation within the app
- **Progress Indicator**: Visual loading feedback
- **Error Handling**: User-friendly error messages

## Technical Specifications

- **Target SDK**: Android 14 (API 34)
- **Minimum SDK**: Android 5.0 (API 21)
- **Package**: com.haatbazar.app
- **Version**: 1.0 (Version Code: 1)

## App Structure

```
app/
├── src/main/
│   ├── java/com/haatbazar/app/
│   │   ├── MainActivity.java      # Main WebView activity
│   │   └── SplashActivity.java    # Splash screen activity
│   ├── res/
│   │   ├── layout/               # UI layouts
│   │   ├── values/               # Colors, strings, themes
│   │   ├── anim/                 # Animations
│   │   └── mipmap/               # App icons
│   └── AndroidManifest.xml       # App configuration
└── build.gradle                  # App dependencies
```

## Key Features

### 🌐 **WebView Configuration**
- JavaScript enabled for full functionality
- DOM storage and local storage support
- Geolocation support
- Mixed content handling
- Custom user agent identification

### 📱 **User Experience**
- Splash screen with animations
- Pull-to-refresh functionality
- Network connectivity checks
- Error handling with retry options
- Back button navigation support

### 🔧 **Technical Features**
- Swipe refresh layout
- Progress bar for loading indication
- Internet connectivity detection
- External link handling
- Memory management

## Build Instructions

1. **Open in Android Studio**
   - Import the `android-app` folder as an Android project

2. **Configure**
   - Ensure Android SDK 34 is installed
   - Sync Gradle files

3. **Build**
   - Build → Generate Signed Bundle/APK
   - Choose APK or App Bundle
   - Sign with your keystore

4. **Install**
   - Install on device or emulator
   - Grant necessary permissions

## Permissions

- **INTERNET**: Access to web content
- **ACCESS_NETWORK_STATE**: Check connectivity
- **CAMERA**: For barcode scanning (if used on website)
- **LOCATION**: For location-based features
- **STORAGE**: For file downloads and caching

## Website Integration

The app loads: `https://coders64.xyz/projects/haatbazar/`

All website functionality is preserved including:
- User authentication
- Shopping cart
- Order management
- Payment processing
- Admin features (when accessed)

## Customization

### Change Website URL
Edit `MainActivity.java`:
```java
private static final String BASE_URL = "YOUR_WEBSITE_URL";
```

### Update App Branding
- Replace app icons in `res/mipmap/`
- Update colors in `res/values/colors.xml`
- Modify strings in `res/values/strings.xml`

### Splash Screen
- Customize `activity_splash.xml` layout
- Update animations in `res/anim/`
- Modify splash duration in `SplashActivity.java`

## Deployment

1. **Generate Release APK**
2. **Test on multiple devices**
3. **Upload to Google Play Store**
4. **Configure store listing**

## Support

For technical support or customization requests, contact the development team.

---

**Haat Bazar Mobile App - Your Family Shopping Destination** 🛒📱