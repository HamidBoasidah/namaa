# دليل البدء السريع - Chat System API

## 🚀 البدء في 5 دقائق

### 1️⃣ استيراد الملفات إلى Postman

1. افتح Postman
2. اضغط على **Import** في الزاوية العلوية اليسرى
3. اسحب وأفلت الملفات التالية:
   - `chat-api.collection.json` (المجموعة الرئيسية)
   - `chat-api.environment.json` (البيئة)

### 2️⃣ تفعيل البيئة

1. في الزاوية العلوية اليمنى، اختر **"Chat API - Local Development"**
2. اضغط على أيقونة العين 👁️ لعرض المتغيرات
3. عدّل القيم حسب بياناتك:
   - `client_email`: بريد العميل
   - `client_password`: كلمة مرور العميل
   - `consultant_email`: بريد المستشار
   - `consultant_password`: كلمة مرور المستشار
   - `booking_id`: معرف حجز موجود (حالة confirmed)

### 3️⃣ تسجيل الدخول

1. افتح مجلد **Auth**
2. اختر **Login (Client)** أو **Login (Consultant)**
3. اضغط **Send**
4. ✅ سيتم حفظ التوكن تلقائياً

### 4️⃣ إنشاء محادثة

1. افتح مجلد **Conversations**
2. اختر **Get or Create Conversation**
3. اضغط **Send**
4. ✅ سيتم حفظ `conversation_id` تلقائياً

### 5️⃣ إرسال رسالة

1. افتح مجلد **Messages**
2. اختر **Send Text Message**
3. عدّل نص الرسالة في `body`
4. اضغط **Send**
5. ✅ تم إرسال الرسالة!

---

## 📋 سيناريوهات الاختبار الجاهزة

### ✅ اختبار حد الرسائل للعميل
```
1. Auth > Login (Client)
2. Test Scenarios > Client - First Out-of-Session Message ✅
3. Test Scenarios > Client - Second Out-of-Session Message ✅
4. Test Scenarios > Client - Third Out-of-Session Message ❌ (403)
```

### ✅ اختبار رسائل المستشار
```
1. Auth > Login (Consultant)
2. Test Scenarios > Consultant - Unlimited Out-of-Session Messages ✅
   (كرر عدة مرات - كلها يجب أن تنجح)
```

### ✅ اختبار الصلاحيات
```
1. Auth > Login (Client) - للحجز A
2. عدّل booking_id للحجز B (حجز آخر)
3. Test Scenarios > Non-Participant Access ❌ (403)
```

---

## 🎯 نصائح سريعة

### 💡 لإرسال مرفقات:
1. اختر **Send Message with Attachments**
2. في `files[]` اضغط **Select Files**
3. اختر ملف (صورة أو PDF)
4. اضغط **Send**

### 💡 لتحميل مرفق:
1. من استجابة الرسالة، انسخ `id` من `attachments`
2. في المتغيرات، عيّن `attachment_id`
3. افتح **Download Attachment**
4. اضغط **Send**

### 💡 لعرض الرسائل:
1. افتح **List Messages**
2. اضغط **Send**
3. ستحصل على جميع الرسائل مع المرفقات

---

## ⚠️ أخطاء شائعة وحلولها

| الخطأ | السبب | الحل |
|------|-------|------|
| 401 | لم تسجل دخول | افتح Auth > Login |
| 403 | لست مشاركاً | تأكد من booking_id صحيح |
| 403 | تجاوزت الحد | أنت عميل وأرسلت 2 رسالة |
| 404 | غير موجود | تحقق من conversation_id |
| 422 | بيانات خاطئة | تحقق من الملفات أو النص |

---

## 📊 هيكل المجموعة

```
Chat System API
├── 🔐 Auth (تسجيل الدخول)
│   ├── Login (Client)
│   └── Login (Consultant)
├── 💬 Conversations (المحادثات)
│   └── Get or Create Conversation
├── 📨 Messages (الرسائل)
│   ├── List Messages
│   ├── Send Text Message
│   ├── Send Message with Attachments
│   └── Send Attachment Only
├── 📎 Attachments (المرفقات)
│   └── Download Attachment
└── 🧪 Test Scenarios (سيناريوهات الاختبار)
    ├── Client - First Out-of-Session Message
    ├── Client - Second Out-of-Session Message
    ├── Client - Third Out-of-Session Message (Should Fail)
    ├── Consultant - Unlimited Out-of-Session Messages
    ├── Non-Participant Access (Should Fail)
    ├── Invalid File Type (Should Fail)
    ├── Empty Message (Should Fail)
    └── Non-Confirmed Booking (Should Fail)
```

---

## 🎓 للمزيد من التفاصيل

راجع الملف الكامل: **CHAT_API_README.md**

---

**✨ جاهز للاختبار! ابدأ الآن بتسجيل الدخول**
