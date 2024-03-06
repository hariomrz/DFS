import React from 'react';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";

class MetaComponent extends React.Component {
    render() {
        const { page } = this.props
        return (

            <Helmet>
                <title>{MetaData[page].title}</title>
                <meta name="description" content={MetaData[page].description} />
                <meta name="keywords" content={MetaData[page].keywords} />
                <meta itemprop="name" content={MetaData[page].title} />
                <meta itemprop="description" content={MetaData[page].keywords} />
                <meta itemprop="image" content="/og-image.jpg" />


                <meta property="og:url" content={process.env.REACT_APP_BASE_URL} />
                <meta property="og:type" content="website" />
                <meta property="og:title" content={MetaData[page].title} />
                <meta property="og:description" content={MetaData[page].keywords} />
                <meta property="og:image" content="/og-image.jpg" />


                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={MetaData[page].title} />
                <meta name="twitter:description" content={MetaData[page].keywords} />
                <meta name="twitter:image" content="/og-image.jpg" />
            </Helmet>

        )
    }

}
MetaComponent.defaultProps = {
    page: '',
}
export default MetaComponent


