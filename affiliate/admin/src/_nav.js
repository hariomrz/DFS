import * as NC from "../src/helper/NetworkingConstants";
import WSManager from "./helper/WSManager";
const items= {
  items: []
};

{
  WSManager.getRole() == 2 && items.items.push({
    name: 'Dashboard',
    url: '/user-affiliate-list',  
  })
}

{
  WSManager.getRole() == 1 && items.items.push({
    name: 'Affiliate Management',
    url: '/affiliate-list',
    children: [
      {
      name: 'View  Affiliate',
      url: '/affiliate-list',
    },
    {
      name: 'Create  Affiliate',
      url: '/create-affilate',

    }
  ],    
  })
}
  
 
  

export default items;
