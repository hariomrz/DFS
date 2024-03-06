import {
  floatingStyles,
  focusStyles,
  inputStyles,
  labelStyles,
} from 'floating-label-react'
import '../assets/fonts/primary_font_Regular.ttf'
import '../assets/fonts/primary_font_Regular.woff'
import '../assets/fonts/primary_font_Regular.woff2'
import '../assets/fonts/Exo2-Bold.woff'
import '../assets/fonts/Exo2-Bold.ttf'
import '../assets/fonts/Exo2-Regular.ttf'
import '../assets/fonts/Exo2-Regular.woff'
import '../assets/fonts/Exo2-Regular.woff2'

export const inputStyle = {
  floating: {
    ...floatingStyles,
    color: '#999',
    fontSize: '12px',
    borderBottomColor: '#EAEAEA',
    fontFamily: 'PrimaryF-Regular',
    textAlign: 'center',
  },
  focus: {
    ...focusStyles,
    borderColor: '#EAEAEA',
  },
  input: {
    ...inputStyles,
    borderBottomWidth: 1,
    borderBottomColor: '#EAEAEA',
    width: '100%',
    fontSize: '16px',
    color: '#212121',
    fontFamily: 'PrimaryF-Regular',
    padding: '16px 0px 10px',
    marginTop: '8px',
    textAlign: 'center',
  },
  label: {
    ...labelStyles,
    paddingBottom: '0px',
    marginBottom: '0px',
    flot: 'center',
    width: '100%',
    fontSize: '14px',
    color: '#999',
    fontWeight: 400,
    fontFamily: 'PrimaryF-Regular',
    textTransform: 'capitalize',
  },
}

export const inputStyleLeft = {
  floating: {
    ...floatingStyles,
    color: '#999',
    fontSize: '12px',
    borderBottomColor: '#EAEAEA',
    fontFamily: 'Exo2-Regular',
    textAlign: 'left',
  },
  focus: {
    ...focusStyles,
    borderColor: '#e1e1e1',
  },
  input: {
    ...inputStyles,
    borderBottomWidth: 1,
    borderBottomColor: '#EAEAEA',
    width: '100%',
    fontSize: '20px',
    color: '#212121',
    fontFamily: 'Exo2-Bold',
    padding: '10px 0px 10px',
    marginTop: '5px',
    textAlign: 'left',
    fontWeight: 'bold',
  },
  label: {
    ...labelStyles,
    paddingBottom: '0px',
    marginBottom: '0px',
    flot: 'left',
    width: '100%',
    fontSize: '14px',
    color: '#999',
    fontWeight: 400,
    // fontFamily: 'PrimaryF-Regular',
  },
}

export const datepicketStyle = {
  label: {
    ...labelStyles,
    paddingBottom: '0px',
    marginBottom: '0px',
    flot: 'left',
    width: '100%',
    fontSize: '12px',
    color: '#999',
    fontWeight: 400,
    fontFamily: 'PrimaryF-Regular',
  },
}
export const selectStyle = {
  label: {
    ...labelStyles,
    paddingBottom: '0px',
    marginBottom: '0px',
    flot: 'left',
    width: '100%',
    fontSize: '12px',
    color: '#999',
    fontWeight: 400,
    fontFamily: 'PrimaryF-Regular',
  },
}
export const mobileNoStyle = {
  label: {
    ...labelStyles,
    paddingBottom: '0px',
    marginBottom: '0px',
    flot: 'left',
    width: '100%',
    fontSize: '12px',
    color: '#999',
    fontWeight: 400,
    fontFamily: 'PrimaryF-Regular',
  },
}
export const darkInputStyle = {
  floating: {
    ...floatingStyles,
    color: '#999',
    fontSize: '12px',
    borderBottomColor: 'rgba(153,153,153,0.4)',
    fontFamily: 'PrimaryF-Regular',
    textAlign: 'center',
  },
  focus: {
    ...focusStyles,
    borderColor: 'rgba(153,153,153,0.4)',
  },
  input: {
    ...inputStyles,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(153,153,153,0.4)',
    width: '100%',
    fontSize: '16px',
    color: '#fff',
    fontFamily: 'PrimaryF-Regular',
    padding: '16px 0px 10px',
    marginTop: '8px',
    textAlign: 'center',
  },
  label: {
    ...labelStyles,
    paddingBottom: '0px',
    marginBottom: '0px',
    flot: 'center',
    width: '100%',
    fontSize: '14px',
    color: '#999',
    fontWeight: 400,
    fontFamily: 'PrimaryF-Regular',
    textTransform: 'capitalize',
  },
}
export const darkInputStyleLeft = {
  floating: {
    ...floatingStyles,
    color: '#999',
    fontSize: '12px',
    borderBottomColor: 'rgba(153,153,153,0.4)',
    fontFamily: 'PrimaryF-Regular',
    textAlign: 'left',
    top: '8px',
  },
  focus: {
    ...focusStyles,
    borderColor: '#e1e1e1',
  },
  input: {
    ...inputStyles,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(153,153,153,0.4)',
    width: '100%',
    fontSize: '16px',
    color: '#FFF',
    fontFamily: 'PrimaryF-Regular',
    padding: '10px 0px 10px',
    marginTop: '10px',
    textAlign: 'left',
  },
  label: {
    ...labelStyles,
    paddingBottom: '0px',
    marginBottom: '0px',
    flot: 'left',
    width: '100%',
    fontSize: '14px',
    color: '#999',
    fontWeight: 400,
    fontFamily: 'PrimaryF-Regular',
  },
}
