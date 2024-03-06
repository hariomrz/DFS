import i18n from "i18next";
import LanguageDetector from "i18next-browser-languagedetector";
import XHR from "i18next-xhr-backend";
import {
  ar,
  en,
  ben,
  mal,
  hi,
  guj,
  fr,
  pun,tam,th,ru,id,tl,zh,kn,es
} from "./locale";

const { DEFAULT_LANG } = process.env

i18n
  .use(XHR)
  .use(LanguageDetector)
  .init({
    debug: false,
    // debug: process.env.NODE_ENV === "development",
    // lng: Utils.getCookie('lang') === '' ? DEFAULT_LANG : Utils.getCookie('lang'),
    fallbackLng: DEFAULT_LANG, // use en if detected lng is not available
    keySeparator: false, // we do not use keys in form messages.welcome
    interpolation: {
      escapeValue: false // react already safes from xss
    },
    languages: ['en', 'ar', 'ben', 'mal','hi','guj','fr','pun','tam','th','ru','id','tl','zh','kn','es'],
    resources: {
      en: {
        translations: en
      },
      ar: {
        translations: ar
      }, ben: {
        translations: ben
      }, mal: {
        translations: mal
      },hi:{
        translations: hi
      },guj:{
        translations: guj
      },fr:{
        translations: fr
      },pun:{
        translations: pun
      },tam:{
        translations: tam
      },th:{
        translations: th
      },ru:{
        translations: ru
      },id:{
        translations: id
      },tl:{
        translations: tl
      },zh:{
        translations: zh
      },kn:{
        translations: kn
      },es:{
        translations: es
      }
    },
    // have a common namespace used around the full app
    ns: ["translations"],
    defaultNS: "translations",
    react: {
      useSuspense: false
    }
  });

export default i18n;