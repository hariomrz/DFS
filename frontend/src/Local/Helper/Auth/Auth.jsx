export function getAuth() {
  return localStorage.getItem('id_token') != null;
}

export function setAuth(auth) {
  localStorage.setItem('id_token', JSON.stringify(auth));
}

export function removeAuth() {
  localStorage.removeItem('id_token');
}


const Auth = {getAuth, setAuth, removeAuth};
export default Auth;