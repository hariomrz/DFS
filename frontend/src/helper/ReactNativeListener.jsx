const ReactNativeListener = withRouter((props) => {
    const Listner = (e) => {
        // Geo Location
        if (e.data.action == 'app_dep_linking' && e.data.type == 'android') {
            props.history.push(pathName);
        }
        if (e.data.action === 'geo_location' && e.data.type == 'android') {
            props.history.push(pathName);
        }
    }
    useEffect(() => {
        window.addEventListener('message', Listner)
        return () => {
            window.removeEventListener('message', Listner)
        }
    }, [])
    return (<></>)
})