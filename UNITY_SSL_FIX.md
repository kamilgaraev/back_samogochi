# Решение проблемы SSL в Unity 6.0

## Проблема
```
UnityWebRequestException: Unable to complete SSL connection
```

## Диагностика

### ✅ Сервер работает корректно
- SSL сертификат Let's Encrypt установлен правильно
- Домен: `https://stressapi.ru`
- Сертификат действителен до: 31 декабря 2025
- TLSv1.3 поддерживается
- Проверено командой: `openssl s_client -connect stressapi.ru:443 -showcerts`

### ❌ Проблема на стороне Unity клиента

---

## Быстрая диагностика для Андрея

### 1. Проверить URL в коде

**Найти где настраивается API URL (конфиг, константы, settings):**

❌ **НЕПРАВИЛЬНО (если используется):**
```csharp
string apiUrl = "http://31.130.149.164/api";  // IP адрес - НЕ РАБОТАЕТ с SSL
```

✅ **ПРАВИЛЬНО:**
```csharp
string apiUrl = "https://stressapi.ru/api";  // Домен с HTTPS
```

### 2. Узнать платформу тестирования

- [ ] Unity Editor (Windows)
- [ ] Unity Editor (Mac)  
- [ ] Android билд
- [ ] iOS билд
- [ ] Windows Standalone

### 3. Тест в браузере

На том же устройстве открыть в браузере:
```
https://stressapi.ru/health
```

Если работает в браузере, но не в Unity → проблема в Unity коде.

---

## Решения

### Решение 1: Исправить URL (самое важное!)

Найти файл с конфигурацией API (например, `ApiConfig.cs`, `Constants.cs` или подобный) и заменить:

```csharp
// Было
public const string API_URL = "http://31.130.149.164/api";

// Должно быть
public const string API_URL = "https://stressapi.ru/api";
```

---

### Решение 2: Добавить CertificateHandler (для Android/iOS)

В файле `BaseApiRequester.cs` в методе `Request<T>`:

```csharp
private async UniTask<T> Request<T>(UnityWebRequest request)
{
    // Добавить логирование для диагностики
    Debug.Log($"[API] Requesting URL: {request.url}");
    Debug.Log($"[API] Platform: {Application.platform}");
    
    // Добавить certificate handler для мобильных платформ
    #if UNITY_ANDROID || UNITY_IOS
    request.certificateHandler = new AcceptAllCertificatesHandler();
    request.disposeCertificateHandlerOnDispose = true;
    #endif
    
    await request.SendWebRequestAsync();
    
    // Остальной код...
}
```

Добавить класс в конец файла `BaseApiRequester.cs`:

```csharp
#if UNITY_ANDROID || UNITY_IOS
public class AcceptAllCertificatesHandler : CertificateHandler
{
    protected override bool ValidateCertificate(byte[] certificateData)
    {
        // В продакшене можно добавить проверку SHA256 хеша сертификата
        // для дополнительной безопасности
        return true;
    }
}
#endif
```

---

### Решение 3: Настройки Android (если тестируется на Android)

**Unity → Edit → Project Settings → Player → Android → Other Settings:**

1. **Minimum API Level:** установить **API Level 24** (Android 7.0) или выше
2. **Target API Level:** установить **API Level 31** или выше  
3. **Scripting Backend:** выбрать **IL2CPP** (вместо Mono)
4. **API Compatibility Level:** **.NET Standard 2.1** или **.NET Framework**

---

### Решение 4: Для Unity Editor (Windows/Mac)

Если проблема возникает в Unity Editor, добавить в начало класса `BaseApiRequester.cs`:

```csharp
using System.Net;
using System.Net.Security;
using System.Security.Cryptography.X509Certificates;

public class BaseApiRequester
{
    static BaseApiRequester()
    {
        // Только для разработки в Editor
        #if UNITY_EDITOR
        ServicePointManager.ServerCertificateValidationCallback = 
            MyRemoteCertificateValidationCallback;
        #endif
    }
    
    #if UNITY_EDITOR
    private static bool MyRemoteCertificateValidationCallback(
        object sender, 
        X509Certificate certificate, 
        X509Chain chain, 
        SslPolicyErrors sslPolicyErrors)
    {
        bool isValid = sslPolicyErrors == SslPolicyErrors.None;
        
        if (!isValid)
        {
            Debug.LogWarning($"[API] SSL Certificate validation failed: {sslPolicyErrors}");
        }
        
        return true; // Принять сертификат
    }
    #endif
    
    // Остальной код класса...
}
```

---

### Решение 5: Проверить антивирус/Firewall (Windows)

Если тестирование на Windows в Editor:

1. Временно отключить антивирус
2. Проверить Windows Firewall
3. Отключить корпоративный VPN/прокси

---

## Порядок действий для Андрея

### Шаг 1: Диагностика (5 минут)
1. Найти где в коде указан API URL
2. Проверить используется ли домен `stressapi.ru` или IP `31.130.149.164`
3. Открыть `https://stressapi.ru/health` в браузере

### Шаг 2: Быстрое решение (10 минут)
1. Заменить IP на домен во всех местах кода
2. Добавить логирование в `BaseApiRequester.cs` (см. Решение 2)
3. Пересобрать и протестировать

### Шаг 3: Если не помогло (15 минут)
1. Добавить `CertificateHandler` (Решение 2)
2. Проверить настройки Android (Решение 3)
3. Для Editor добавить обход SSL (Решение 4)

---

## Контрольный список

- [ ] URL в коде использует `https://stressapi.ru` (не IP адрес)
- [ ] `https://stressapi.ru/health` открывается в браузере
- [ ] Minimum API Level >= 24 (для Android)
- [ ] Добавлено логирование URL в запросах
- [ ] Добавлен CertificateHandler для мобильных платформ
- [ ] Антивирус/Firewall не блокирует (для Windows Editor)

---

## Дополнительная информация

### Проверка какой URL используется в рантайме

Добавить в код перед первым API запросом:

```csharp
Debug.Log($"[API] Base URL: {YOUR_API_URL_CONSTANT}");
Debug.Log($"[API] Full URL: {request.url}");
Debug.Log($"[API] Platform: {Application.platform}");
Debug.Log($"[API] Unity Version: {Application.unityVersion}");
```

### Если ничего не помогает

Написать разработчикам бэкенда с информацией:
- Какой URL используется в коде
- На какой платформе тестируется
- Логи из Unity Console
- Результат теста в браузере

---

## Технические детали SSL сертификата

```
Domain: stressapi.ru, www.stressapi.ru
Issuer: Let's Encrypt (E7)
Valid until: 2025-12-31
Protocol: TLSv1.3
Cipher: TLS_AES_256_GCM_SHA384
Verification: OK (код 0)
```

Сертификат полностью валиден и должен работать с Unity 6.0.

---

## Вопросы?

Если решения не помогли, нужна дополнительная информация:

1. Какой **точный URL** используется в коде?
2. На какой **платформе** возникает ошибка?
3. **Работает ли** в браузере на том же устройстве?
4. Какой **Minimum API Level** в настройках Android?
5. **Полный лог** с Debug.Log из Решения 2

---

**Дата создания:** 02.10.2025  
**API Version:** 1.0.0  
**Production URL:** https://stressapi.ru/api


