import i18n from "i18next";
import Backend from "i18next-xhr-backend";
import { initReactI18next } from "react-i18next";
import LanguageDetector from "i18next-browser-languagedetector";
import * as Constants from "./helper/Constants";

import translationEng from "./assets/i18n/translations/en.json";
import translationHi from "./assets/i18n/translations/hi.json";
import translationGuj from "./assets/i18n/translations/guj.json";
import translationFr from "./assets/i18n/translations/fr.json";
import translationBen from "./assets/i18n/translations/ben.json";
import translationPun from "./assets/i18n/translations/pun.json";
import translationTam from "./assets/i18n/translations/tam.json";
import translationTh from "./assets/i18n/translations/th.json";
import translationRu from "./assets/i18n/translations/ru.json";
import translationId from "./assets/i18n/translations/id.json";
import translationTl from "./assets/i18n/translations/tl.json";
import translationZh from "./assets/i18n/translations/zh.json";
import translationKn from "./assets/i18n/translations/kn.json";
import translationEs from "./assets/i18n/translations/es.json";

import { hi,guj,ben,es,fr,id,kn,pun,ru,tam,th,tl,zh } from "Local/i18n/locale";

Constants.setValue.setLanguage([
  { value: "en", label: "English", desc: "English" },
  { value: "hi", label: "हिंदी", desc: "Hindi" },
  { value: "guj", label: "ગુજરાતી", desc: "Gujarati" },
  { value: "fr", label: "Français", desc: "French" },
  { value: "ben", label: "বাংলা", desc: "Bengali" },
  { value: "pun", label: "ਪੰਜਾਬੀ", desc: "Punjabi" },
  { value: "tam", label: "தமிழ்", desc: "Tamil" },
  { value: "th", label: "ไทย", desc: "Thai" },
  { value: "ru", label: "русский", desc: "Russian" },
  { value: "id", label: "bahasa Indonesia", desc: "Indonesian" },
  { value: "tl", label: "Tagalog", desc: "Tagalog" },
  { value: "zh", label: "中国人", desc: "Chinese" },
  { value: "kn", label: "ಕನ್ನಡ", desc: "Kannada" },
  { value: "es", label: "Española", desc: "Spanish" }
])

i18n
  .use(LanguageDetector)
  .use(Backend)
  .use(initReactI18next)
  .init({
    debug: false,
    // lng: localStorage.getItem('i18nextLng') ? 'en' : localStorage.getItem('i18nextLng'),
    fallbackLng: "en", // use en if detected lng is not available
    resources: {
      en: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ? translationEng : Constants.LANGUAGE_OBJ
      },
      hi: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ? {...translationHi, ...hi} : Constants.LANGUAGE_OBJ, 
      },
      guj: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ? {...translationGuj, ...guj} : Constants.LANGUAGE_OBJ
      },
      fr: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ? {...translationFr,...fr} : Constants.LANGUAGE_OBJ
      },
      ben: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ? {...translationBen,...ben} : Constants.LANGUAGE_OBJ
      },
      pun: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ?{...translationPun,...pun }: Constants.LANGUAGE_OBJ
      },
      tam: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ? {...translationTam,...tam} : Constants.LANGUAGE_OBJ
      },
      th: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ? {...translationTh,...th} : Constants.LANGUAGE_OBJ
      },
      ru: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ? {...translationRu,...ru} : Constants.LANGUAGE_OBJ
      },
      id: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ? {...translationId,...id} : Constants.LANGUAGE_OBJ
      },
      tl: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ? {...translationTl,...tl} : Constants.LANGUAGE_OBJ
      },
      zh: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ? {...translationZh,...zh} : Constants.LANGUAGE_OBJ
      },
      kn: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ? {...translationKn,...kn} : Constants.LANGUAGE_OBJ
      },
      es: {
        translations: process.env.REACT_APP_SERVE_LANG_LOCALLY == '1' ?{...translationEs,...es} : Constants.LANGUAGE_OBJ 
      }
    },
    /* can have multiple namespace, in case you want to divide a huge translation into smaller pieces and load them on demand */
    ns: ["translations"],
    defaultNS: "translations",
    keySeparator: false,
    returnObjects: true,
    interpolation: {
      escapeValue: false,
      formatSeparator: ","
    },
    react: {
      wait: true,
      useSuspense: false,
    }
  });

export default i18n;
