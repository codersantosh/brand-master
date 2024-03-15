/* WordPress */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/* Library */
import classNames from 'classnames';

import { cloneDeep } from 'lodash';

/*Atrc*/
import {
	AtrcText,
	AtrcWireFrameContentSidebar,
	AtrcWireFrameHeaderContentFooter,
	AtrcPrefix,
	AtrcPanelBody,
	AtrcPanelRow,
	AtrcContent,
	AtrcTitleTemplate1,
	AtrcControlText,
	AtrcControlToggle,
	AtrcNestedObjUpdateByKey2,
} from 'atrc';

/* Inbuilt */
import { AtrcReduxContextData } from '../../../routes';
import { DocsTitle } from '../../../components/molecules';

/*Local*/
const MainContent = () => {
	const data = useContext(AtrcReduxContextData);

	const { dbSettings, dbUpdateSetting } = data;

	const { login = {} } = dbSettings;
	const { url = {} } = login;
	const { on: urlOn = false, slug = '', redirect_slug = '' } = url;

	return (
		<AtrcContent>
			<AtrcPanelRow>
				<AtrcControlToggle
					label={__(
						'Enhance the security of your website by modifying the login URL and restricting access to the wp-login.php page and wp-admin directory for not logged in users.',
						'brand-master'
					)}
					checked={urlOn}
					onChange={() => {
						const updatedSettings = AtrcNestedObjUpdateByKey2({
							settings: login,
							key1: 'url',
							key2: 'on',
							val2: !urlOn,
						});
						dbUpdateSetting('login', updatedSettings);
					}}
				/>
			</AtrcPanelRow>
			{urlOn ? (
				<>
					<AtrcPanelRow className={classNames('at-m')}>
						<AtrcControlText
							label={__('Login page slug', 'brand-master')}
							help={
								<>
									{__(
										'Secure your website by altering the login URL and restricting access to the wp-login.php page and the wp-admin directory to authorized users only, enhancing protection against unauthorized access.',
										'brand-master'
									)}
									<br />
									<b>{__('Login url: ', 'brand-master')}</b>
									{slug ? (
										<>
											{brandMasterLocalize.home_url}
											<code>{slug}</code>
										</>
									) : (
										brandMasterLocalize.home_url + 'wp-login.php'
									)}
								</>
							}
							value={slug}
							onChange={(newVal) => {
								const updatedSettings = AtrcNestedObjUpdateByKey2({
									settings: login,
									key1: 'url',
									key2: 'slug',
									val2: newVal,
								});
								dbUpdateSetting('login', updatedSettings);
							}}
						/>
					</AtrcPanelRow>
					<AtrcPanelRow className={classNames('at-m')}>
						<AtrcControlText
							label={__('Redirection slug', 'brand-master')}
							help={
								<>
									{__(
										'Redirect the URL for unauthorized attempts to access the wp-login.php page and the wp-admin directory, ensuring redirection occurs when users are not logged in. Use `404` for loading 404 template.',
										'brand-master'
									)}
									<br />
									<b>{__('Redirection url: ', 'brand-master')}</b>
									{redirect_slug && '404' !== redirect_slug ? (
										<>
											{brandMasterLocalize.home_url}
											<code>{redirect_slug}</code>
										</>
									) : (
										__('404 template', 'brand-master')
									)}
								</>
							}
							value={redirect_slug}
							onChange={(newVal) => {
								const updatedSettings = AtrcNestedObjUpdateByKey2({
									settings: login,
									key1: 'url',
									key2: 'redirect_slug',
									val2: newVal,
								});
								dbUpdateSetting('login', updatedSettings);
							}}
						/>
					</AtrcPanelRow>
				</>
			) : null}
		</AtrcContent>
	);
};

const Documentation = () => {
	const data = useContext(AtrcReduxContextData);

	const { lsSettings, lsSaveSettings } = data;

	return (
		<AtrcWireFrameHeaderContentFooter
			headerRowProps={{
				className: classNames(AtrcPrefix('header-docs'), 'at-m'),
			}}
			renderHeader={
				<DocsTitle
					onClick={() => {
						const localStorageClone = cloneDeep(lsSettings);
						localStorageClone.bmLtDocs1 = !localStorageClone.bmLtDocs1;
						lsSaveSettings(localStorageClone);
					}}
				/>
			}
			renderContent={
				<>
					<AtrcPanelBody
						className={classNames(AtrcPrefix('m-0'))}
						title={__(
							'How can I enhance the security of my WordPress website?',
							'brand-master'
						)}
						initialOpen={true}>
						<AtrcText
							tag='p'
							className={classNames(AtrcPrefix('m-0'), 'at-m')}>
							{__(
								'Utilize security and URL settings to modify the page URL slug of your WordPress login page, providing an additional layer of protection against unauthorized access.',
								'brand-master'
							)}
						</AtrcText>
					</AtrcPanelBody>
					<AtrcPanelBody
						title={__(
							'Why is it important to change the login URL and restrict access to certain pages?',
							'brand-master'
						)}
						initialOpen={true}>
						<AtrcText
							tag='p'
							className={classNames(AtrcPrefix('m-0'), 'at-m')}>
							{__(
								'Changing the login URL and limiting access to sensitive areas such as wp-login.php and wp-admin directory helps safeguard your website against unauthorized access attempts, enhancing overall security.',
								'brand-master'
							)}
						</AtrcText>
					</AtrcPanelBody>
				</>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

const Settings = () => {
	const data = useContext(AtrcReduxContextData);
	const { lsSettings } = data;

	const { bmLtDocs1 } = lsSettings;

	return (
		<AtrcWireFrameHeaderContentFooter
			wrapProps={{
				className: classNames(AtrcPrefix('bg-white'), 'at-bg-cl'),
			}}
			renderHeader={
				<AtrcTitleTemplate1
					title={__('WordPress login page settings', 'brand-master')}
				/>
			}
			renderContent={
				<AtrcWireFrameContentSidebar
					wrapProps={{
						allowContainer: true,
						type: 'fluid',
						tag: 'section',
					}}
					renderContent={<MainContent />}
					renderSidebar={!bmLtDocs1 ? <Documentation /> : null}
					contentProps={{
						contentCol: bmLtDocs1 ? 'at-col-12' : 'at-col-7',
					}}
					sidebarProps={{
						sidebarCol: 'at-col-5',
					}}
				/>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

export default Settings;
