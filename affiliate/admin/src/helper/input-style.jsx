import FloatingLabel, {
    floatingStyles,
    focusStyles,
    inputStyles,
    labelStyles
} from 'floating-label-react'

export const inputStyle = {
    floating: {
        ...floatingStyles,
        color: '#696969',
        fontSize: '12px',
        borderBottomColor: '#e1e1e1',
        fontFamily: 'MuliRegular',
    },
    focus: {
        ...focusStyles,
        borderColor: '#e1e1e1',
    },
    input: {
        ...inputStyles,
        borderBottomWidth: 2,
        borderBottomColor: '#e1e1e1',
        width: '100%',
        fontSize: '14px',
        color: '#333333',
        fontFamily: 'MuliBold',
        padding: '16px 0px 10px'
    },
    label: {
        ...labelStyles,
        paddingBottom: '0px',
        marginBottom: '0px',
        width: '100%',
        fontSize: '12px',
        color: '#696969',
        fontFamily: 'MuliRegular',
    }
}